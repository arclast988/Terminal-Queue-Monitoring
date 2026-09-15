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
        $toEmail = $config->recipients;
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

        // Use CodeIgniter Email library
        $emailSvc = $this->getConfiguredEmailService();
        $emailSvc->setReplyTo($email, $name); // Important: Guest's email goes here
        $emailSvc->setTo($toEmail);
        $emailSvc->setSubject($subjectLine);
        $emailSvc->setMessage(nl2br(esc($body)));

        if ($emailSvc->send()) {
            $cache->save($cacheKey, $attempts + 1, 300); // 5 minutes TTL
            return redirect()->to(base_url('guest') . '#support-section')
                ->with('contact_success', 'Your message has been sent! We will respond shortly.');
        } else {
            // Fallback – still store session so user knows what happened
            return redirect()->back()
                ->with('contact_error', 'Message could not be sent. Please try contacting us directly at ' . $config->recipients);
        }
    }
}
