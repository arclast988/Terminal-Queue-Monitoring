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

        $toEmail = 'marquezgio38@gmail.com';
        $subjectLine = ($type === 'report')
            ? '[PTM Report Issue] ' . ($subject ?: 'Issue reported via website')
            : '[PTM Contact Us] ' . ($subject ?: 'Message from website');

        $body = "Name: {$name}\nEmail: {$email}\n\nMessage:\n{$message}";

        // Use CodeIgniter Email library
        $emailSvc = \Config\Services::email();
        $config = config('Email');

        // Note: Gmail SMTP requires the 'From' address to be the authenticated email
        $fromEmail = $config->SMTPUser ?: 'marquezgio38@gmail.com';
        
        $emailSvc->setFrom($fromEmail, 'PTM System Feedback');
        $emailSvc->setReplyTo($email, $name); // Important: Guest's email goes here
        $emailSvc->setTo($toEmail);
        $emailSvc->setSubject($subjectLine);
        $emailSvc->setMessage(nl2br(esc($body)));

        if ($emailSvc->send()) {
            return redirect()->to(base_url('guest') . '#support-section')
                ->with('contact_success', 'Your message has been sent! We will respond shortly.');
        } else {
            // Fallback – still store session so user knows what happened
            return redirect()->back()
                ->with('contact_error', 'Message could not be sent. Please try contacting us directly at marquezgio38@gmail.com');
        }
    }
}
