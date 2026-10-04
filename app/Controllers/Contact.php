<?php

namespace App\Controllers;

class Contact extends BaseController
{
    private const PENDING = 'guest_contact_pending';

    public function send()
    {
        if (!$this->request->is('post')) return redirect()->to(base_url('guest'));
        $draft = [];
        foreach (['type', 'name', 'email', 'subject', 'message'] as $field) {
            $value = $this->request->getPost($field);
            $draft[$field] = is_string($value) ? trim($value) : '';
        }
        if (!in_array($draft['type'], ['report', 'contact'], true)) return $this->returnToForm($draft, 'Choose Contact Us or Report Issue to send your message.');
        if ($draft['name'] === '' || $draft['email'] === '' || $draft['message'] === '') return $this->returnToForm($draft, 'Please fill in all required fields.');
        if (strlen($draft['email']) > 254 || !filter_var($draft['email'], FILTER_VALIDATE_EMAIL)) return $this->returnToForm($draft, 'Please provide a valid email address.');
        if (mb_strlen($draft['name']) > 120 || mb_strlen($draft['subject']) > 180 || mb_strlen($draft['message']) > 5000 || preg_match('/[\r\n]/', $draft['name'] . $draft['subject'])) {
            return $this->returnToForm($draft, 'Use up to 120 characters for your name, 180 for the subject, and 5,000 for the message. Names and subjects must be on one line.');
        }
        $recipient = $this->supportRecipient();
        if ($recipient === null) return $this->returnToForm($draft, 'The terminal contact email is currently unavailable. Please contact the terminal office directly.');
        if ($issue = $this->reserveCodeRequest($draft['email'])) return $this->returnToForm($draft, $issue);
        $code = sprintf('%06d', random_int(0, 999999));
        if (!$this->sendVerificationCode($draft['email'], $code)) {
            return $this->returnToForm($draft, 'We could not send your verification code. Check your email address and try again in 60 seconds, or contact the terminal office directly. Your message has not been sent.');
        }
        session()->set(self::PENDING, $draft + [
            'id' => bin2hex(random_bytes(32)), 'recipient' => $recipient,
            'code_hash' => password_hash($code, PASSWORD_DEFAULT), 'verified' => false,
            'expires_at' => $this->now() + 600, 'sent_at' => $this->now(), 'attempts' => 0,
        ]);
        return redirect()->to(base_url('contact/verify'));
    }

    public function verification()
    {
        if (($pending = $this->pending()) === null) return $this->expired();
        $this->response->setHeader('Cache-Control', 'no-store');
        return view('public/contact-verification', ['title' => 'Verify your email', 'pending' => $pending,
            'resendWait' => max(0, $pending['sent_at'] + 60 - $this->now())]);
    }

    public function verify()
    {
        if (($pending = $this->pending()) === null) return $this->expired();
        if (!$this->matchesDraft($pending)) return $this->verificationError('This verification page is out of date. Use the code for the message shown below.');
        // Verify only the saved message/address. Submitted replacements are ignored.
        if (!$pending['verified']) {
            $code = $this->request->getPost('code');
            if (!is_string($code) || !preg_match('/^\d{6}$/D', $code)) return $this->verificationError('Enter the complete six-digit code from your email.');
            if (!password_verify($code, $pending['code_hash'])) {
                $pending['attempts']++;
                if ($pending['attempts'] >= 5) {
                    session()->remove(self::PENDING);
                    return $this->returnToForm($pending, 'Too many incorrect codes. Request a new verification code to send your message.');
                }
                session()->set(self::PENDING, $pending);
                return $this->verificationError('Incorrect code. ' . (5 - $pending['attempts']) . ' attempts remaining.');
            }
            $pending['verified'] = true;
            $pending['code_hash'] = null;
            session()->set(self::PENDING, $pending);
        }
        $cache = cache();
        $rateKey = 'contact_rate_limit_' . hash('sha256', $this->request->getIPAddress());
        $attempts = (int) $cache->get($rateKey);
        if ($attempts >= 3) return $this->verificationError('Too many messages sent. Please wait five minutes before trying again.');
        $report = $pending['type'] === 'report';
        $subjectLine = '[' . app_acronym() . ($report ? ' Report Issue] ' : ' Contact Us] ')
            . ($pending['subject'] ?: ($report ? 'Issue reported via website' : 'Message from website'));
        $topic = $report ? 'Issue Type' : 'Subject';
        $body = "Name: {$pending['name']}\nEmail: {$pending['email']}\nEmail verified: Yes\n{$topic}: {$pending['subject']}\n\nMessage:\n{$pending['message']}";
        // The session handler locks concurrent requests. Consume before delivery,
        // restoring only this verified draft if the transport reports failure.
        session()->remove(self::PENDING);
        if (!$this->sendConfiguredHtmlEmail($pending['recipient'], $subjectLine, nl2br(esc($body)), $pending['email'], $pending['name'])) {
            session()->set(self::PENDING, $pending);
            return $this->verificationError('Your email is verified, but the message could not be sent. Try Send message again, or contact the terminal office directly.');
        }
        $cache->save($rateKey, $attempts + 1, 300);
        return redirect()->to($this->formUrl($pending['type']))->with('contact_success', 'Your email was verified and your message has been sent to terminal management.');
    }

    public function resend()
    {
        if (($pending = $this->pending()) === null) return $this->expired();
        if (!$this->matchesDraft($pending)) return $this->verificationError('This verification page is out of date.');
        if ($pending['verified']) return $this->verificationError('Your email is already verified. Use Send message to retry delivery.');
        if ($this->now() < $pending['sent_at'] + 60) return $this->verificationError('Please wait 60 seconds before requesting another code.');
        if ($issue = $this->reserveCodeRequest($pending['email'])) return $this->verificationError($issue);
        $code = sprintf('%06d', random_int(0, 999999));
        if (!$this->sendVerificationCode($pending['email'], $code)) return $this->verificationError('We could not send a new code. Your previous code is still usable until it expires. Try again in 60 seconds.');
        $pending['code_hash'] = password_hash($code, PASSWORD_DEFAULT);
        $pending['sent_at'] = $this->now();
        $pending['expires_at'] = $this->now() + 600;
        // Resending must not reset the number of wrong guesses.
        session()->set(self::PENDING, $pending);
        return redirect()->to(base_url('contact/verify'))->with('contact_verify_notice', 'A new code has been sent. Use the latest code in your email.');
    }

    public function edit()
    {
        if (($pending = $this->pending()) === null) return $this->expired();
        if (!$this->matchesDraft($pending)) return $this->verificationError('This verification page is out of date.');
        session()->remove(self::PENDING);
        return $this->returnToForm($pending);
    }

    protected function now(): int
    {
        return time();
    }

    protected function supportRecipient(): ?string
    {
        $config = config('Email');
        foreach ([$config->recipients, app_contact_email(), $config->fromEmail ?: $config->SMTPUser] as $candidate) {
            $candidate = trim($candidate);
            if (filter_var($candidate, FILTER_VALIDATE_EMAIL)) return $candidate;
        }
        return null;
    }

    private function sendVerificationCode(string $email, string $code): bool
    {
        $html = '<div style="font-family:Arial,sans-serif;max-width:520px;margin:auto;padding:24px">'
            . '<h2>Confirm your email</h2><p>Use this code to send your contact message or issue report to '
            . esc(app_name()) . '.</p><p style="font-size:32px;font-weight:bold;letter-spacing:6px">' . $code
            . '</p><p>This code expires in 10 minutes. Your message will be sent only after you confirm the code.</p>'
            . '<p>If you did not request this code, you can ignore this email.</p></div>';
        return $this->sendConfiguredHtmlEmail($email, '[' . app_acronym() . '] Confirm your email', $html);
    }

    private function pending(): ?array
    {
        $pending = session()->get(self::PENDING);
        return is_array($pending) && ($pending['expires_at'] ?? 0) > $this->now() ? $pending : null;
    }

    private function matchesDraft(array $pending): bool
    {
        $id = $this->request->getPost('draft_id');
        return is_string($id) && hash_equals($pending['id'], $id);
    }

    private function expired()
    {
        $pending = session()->get(self::PENDING);
        session()->remove(self::PENDING);
        return $this->returnToForm(is_array($pending) ? $pending : [], 'Email verification has expired or has already been used. Enter your message to request a new code.');
    }

    private function verificationError(string $message)
    {
        return redirect()->to(base_url('contact/verify'))->with('contact_verify_error', $message);
    }

    private function formUrl(string $type): string
    {
        return base_url('guest') . ($type === 'report' ? '?report=1' : '?contact=1') . '#support-section';
    }

    private function returnToForm(array $draft, ?string $error = null)
    {
        $input = array_intersect_key($draft, array_flip(['type', 'name', 'email', 'subject', 'message']));
        session()->setFlashdata('_ci_old_input', ['get' => [], 'post' => $input]);
        $response = redirect()->to($this->formUrl($draft['type'] ?? 'contact'));
        return $error === null ? $response : $response->with('contact_error', $error);
    }

    /** Limit email deliveries across sessions by address and IP before sending. */
    private function reserveCodeRequest(string $email): ?string
    {
        $cache = cache();
        $keys = ['ip' => 'contact_code_ip_' . hash('sha256', $this->request->getIPAddress()),
            'email' => 'contact_code_email_' . hash('sha256', strtolower($email))];
        $records = [];
        foreach ($keys as $scope => $key) {
            $record = $cache->get($key);
            $records[$scope] = is_array($record) && $record['expires_at'] > $this->now()
                ? $record : ['count' => 0, 'expires_at' => $this->now() + 300, 'last_at' => 0];
            if ($records[$scope]['count'] >= 3) return 'Too many verification requests. Please wait five minutes before requesting another code.';
        }
        if ($records['email']['last_at'] > $this->now() - 60) return 'Please wait 60 seconds before requesting another code for this email address.';
        foreach ($keys as $scope => $key) {
            $records[$scope]['count']++;
            $records[$scope]['last_at'] = $this->now();
            $cache->save($key, $records[$scope], max(1, $records[$scope]['expires_at'] - $this->now()));
        }
        return null;
    }
}
