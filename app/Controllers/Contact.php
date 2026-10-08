<?php

namespace App\Controllers;

class Contact extends BaseController
{
    public function send()
    {
        if (!$this->request->is('post')) return redirect()->to(base_url('guest'));
        $draft = [];
        foreach (['type', 'name', 'subject', 'message'] as $field) {
            $value = $this->request->getPost($field);
            $draft[$field] = is_string($value) ? trim($value) : '';
        }
        if (!in_array($draft['type'], ['report', 'contact'], true)) return $this->formResponse($draft, 'Choose Contact Us or Report Issue to prepare your message.');
        if ($draft['name'] === '' || $draft['message'] === '') return $this->formResponse($draft, 'Please fill in all required fields.');
        if (mb_strlen($draft['name']) > 120 || mb_strlen($draft['subject']) > 180 || mb_strlen($draft['message']) > 5000 || preg_match('/[\r\n]/', $draft['name'] . $draft['subject'])) {
            return $this->formResponse($draft, 'Use up to 120 characters for your name, 180 for the subject, and 5,000 for the message. Names and subjects must be on one line.');
        }
        $recipient = $this->supportRecipient();
        if ($recipient === null) return $this->formResponse($draft, 'The terminal contact email is currently unavailable. Please contact the terminal office directly.');
        $label = $draft['type'] === 'report' ? 'Report Issue' : 'Contact Us';
        $subject = $draft['subject'] !== '' ? ': ' . $draft['subject'] : '';
        $body = 'Name: ' . $draft['name'] . "\nType: " . $label
            . ($draft['subject'] !== '' ? "\nSubject: " . $draft['subject'] : '')
            . "\n\nMessage:\n" . $draft['message'];
        $url = 'https://mail.google.com/mail/?' . http_build_query([
            'view'=>'cm', 'fs'=>'1', 'tf'=>'cm', 'to'=>$recipient,
            'su'=>'[' . app_acronym() . '] ' . $label . $subject, 'body'=>$body,
        ], '', '&', PHP_QUERY_RFC3986);
        session()->remove('guest_contact_pending');
        return $this->formResponse($draft, null, $url);
    }

    // Old links and already-open verification forms must never send system email.
    public function verification() { return $this->legacyDraft(); }
    public function verify() { return $this->legacyDraft(); }
    public function resend() { return $this->legacyDraft(); }
    public function edit() { return $this->legacyDraft(); }

    protected function supportRecipient(): ?string
    {
        return app_contact_recipient();
    }

    private function legacyDraft()
    {
        $pending = session()->get('guest_contact_pending');
        session()->remove('guest_contact_pending');
        return $this->formResponse(is_array($pending) ? $pending : [], 'Open a Gmail draft to send your message from your own account. Email verification is no longer needed.');
    }

    private function formResponse(array $draft, ?string $error = null, ?string $url = null)
    {
        $input = array_intersect_key($draft, array_flip(['type', 'name', 'subject', 'message']));
        $type = ($input['type'] ?? '') === 'report' ? 'report' : 'contact';
        $this->response->setHeader('Cache-Control', 'no-store');
        if ($this->request->isAJAX() || str_contains(strtolower($this->request->getHeaderLine('Accept')), 'application/json')) {
            return $this->response->setJSON(['success'=>$error === null, 'stage'=>$url ? 'draft' : 'form',
                'type'=>$type, 'draft'=>$input, 'message'=>$error, 'gmailUrl'=>$url,
                'csrf'=>['name'=>csrf_token(), 'hash'=>csrf_hash()]]);
        }
        session()->setFlashdata('_ci_old_input', ['get'=>[], 'post'=>$input]);
        session()->remove('contact_gmail_url');
        if ($url !== null) session()->setFlashdata('contact_gmail_url', $url);
        $response = redirect()->to(base_url('guest') . '?' . $type . '=1#support-section');
        $response->setHeader('Cache-Control', 'no-store');
        return $error === null ? $response : $response->with('contact_error', $error);
    }
}
