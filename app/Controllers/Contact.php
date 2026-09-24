<?php

namespace App\Controllers;

class Contact extends BaseController
{
    public function send()
    {
        // Only accept POST requests
        if (!$this->request->is('post')) {
            return redirect()->to(base_url('guest') . '#support-section');
        }

        // Rate limiting: max 3 contact submissions per 5 minutes per IP
        $cache = \Config\Services::cache();
        $ip = $this->request->getIPAddress();
        $cacheKey = 'contact_rate_limit_' . hash('sha256', $ip);
        $attempts = (int) $cache->get($cacheKey);
        
        if ($attempts >= 3) {
            return redirect()->back()->withInput()->with('contact_error', 'Too many messages sent. Please wait 5 minutes before trying again.');
        }

        $type    = $this->request->getPost('type');    // 'report' or 'contact'
        $name    = trim($this->request->getPost('name'));
        $email   = trim($this->request->getPost('email'));
        $subject = trim($this->request->getPost('subject'));
        $message = trim($this->request->getPost('message'));

        if (!$name || !$email || !$message) {
            return redirect()->back()->with('contact_error', 'Please fill in all required fields.');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return redirect()->back()->with('contact_error', 'Please provide a valid email address.');
        }

        $config = config('Email');
        $toEmail = trim($config->recipients);
        if ($toEmail === '' || !filter_var($toEmail, FILTER_VALIDATE_EMAIL)) {
            $toEmail = trim(app_contact_email());
        }
        if ($toEmail === '' || !filter_var($toEmail, FILTER_VALIDATE_EMAIL)) {
            $toEmail = trim($config->fromEmail ?: $config->SMTPUser);
        }

        if ($toEmail === '' || !filter_var($toEmail, FILTER_VALIDATE_EMAIL)) {
            log_message('error', 'Contact submission failed: No destination recipient email configured.');
            $supportPhone = app_contact_phone() ? ' (' . app_contact_phone() . ')' : '';
            return redirect()->back()->withInput()
                ->with('contact_error', 'The terminal contact email is not currently configured on the server. Please visit the terminal office or contact us by phone' . $supportPhone . '.');
        }

        $acro = app_acronym();
        $subjectLine = ($type === 'report')
            ? '[' . $acro . ' Report Issue] ' . ($subject ?: 'Issue reported via website')
            : '[' . $acro . ' Contact Us] ' . ($subject ?: 'Message from website');

        if ($type === 'report') {
            $issueType = $subject ?: 'General Issue';
            $body = "Name: {$name}\nEmail: {$email}\nIssue Type: {$issueType}\n\nMessage:\n{$message}";
        } else {
            $topic = $subject ?: 'General Inquiry';
            $body = "Name: {$name}\nEmail: {$email}\nSubject: {$topic}\n\nMessage:\n{$message}";
        }

        if ($this->sendConfiguredHtmlEmail($toEmail, $subjectLine, nl2br(esc($body)), $email, $name)) {
            $cache->save($cacheKey, $attempts + 1, 300); // 5 minutes TTL
            return redirect()->to(base_url('guest') . '#support-section')
                ->with('contact_success', 'Your message has been sent! We will respond shortly.');
        } else {
            // Fallback – still store session so user knows what happened
            $displayEmail = ($toEmail !== '' && filter_var($toEmail, FILTER_VALIDATE_EMAIL)) ? $toEmail : 'the terminal management office';
            return redirect()->back()->withInput()
                ->with('contact_error', 'Message could not be sent. Email delivery is unconfigured or unavailable on the server. Please contact us directly at ' . $displayEmail . '.');
        }
    }
}
