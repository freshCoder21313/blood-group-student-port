<?php

namespace Database\Seeders;

use App\Models\SitePage;
use App\Models\SiteSetting;
use Illuminate\Database\Seeder;

class SiteSettingsSeeder extends Seeder
{
    public function run(): void
    {
        // Site Settings
        $settings = [
            // General
            ['key' => 'school_name', 'value' => 'SKS Academy', 'group' => 'general'],
            ['key' => 'school_tagline', 'value' => 'Excellence in Education', 'group' => 'general'],

            // Contact
            ['key' => 'school_email', 'value' => 'info@sks.edu', 'group' => 'contact'],
            ['key' => 'school_phone', 'value' => '+254 700 000 000', 'group' => 'contact'],
            ['key' => 'school_address', 'value' => 'P.O. Box 12345, Nairobi, Kenya', 'group' => 'contact'],
            ['key' => 'school_website', 'value' => 'https://sks.edu', 'group' => 'contact'],

            // Footer
            ['key' => 'footer_copyright', 'value' => '© {year} {app_name}. All rights reserved.', 'group' => 'footer'],
            ['key' => 'footer_description', 'value' => 'Student Admission Portal — Apply, track, and manage your academic journey online.', 'group' => 'footer'],
        ];

        foreach ($settings as $setting) {
            SiteSetting::firstOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }

        // Default System Pages
        $pages = [
            [
                'title' => 'Terms of Service',
                'slug' => 'terms',
                'is_system' => true,
                'content' => '<h2>Terms of Service</h2>
<p>Welcome to SKS Academy\'s Student Admission Portal. By accessing and using this portal, you agree to be bound by the following terms and conditions.</p>

<h3>1. Acceptance of Terms</h3>
<p>By registering an account and submitting an application through this portal, you acknowledge that you have read, understood, and agree to abide by these terms.</p>

<h3>2. User Accounts</h3>
<p>You are responsible for maintaining the confidentiality of your account credentials. You must provide accurate and complete information during registration.</p>

<h3>3. Application Process</h3>
<p>All applications submitted through this portal are subject to review by the admissions committee. Submission of an application does not guarantee admission.</p>

<h3>4. Data Accuracy</h3>
<p>You certify that all information provided in your application is true, complete, and accurate. Providing false information may result in the rejection of your application or revocation of admission.</p>

<h3>5. Payment Policy</h3>
<p>Application fees are non-refundable once payment has been processed. Ensure all details are correct before submitting payment.</p>

<h3>6. Modifications</h3>
<p>SKS Academy reserves the right to modify these terms at any time. Continued use of the portal constitutes acceptance of any changes.</p>',
            ],
            [
                'title' => 'Privacy Policy',
                'slug' => 'privacy',
                'is_system' => true,
                'content' => '<h2>Privacy Policy</h2>
<p>SKS Academy is committed to protecting your personal information. This policy explains how we collect, use, and safeguard your data.</p>

<h3>1. Information We Collect</h3>
<p>We collect personal information that you provide during registration and the application process, including:</p>
<ul>
    <li>Name, email address, and phone number</li>
    <li>National identification details</li>
    <li>Academic records and certificates</li>
    <li>Payment information</li>
</ul>

<h3>2. How We Use Your Information</h3>
<p>Your information is used to:</p>
<ul>
    <li>Process and evaluate your admission application</li>
    <li>Communicate with you about your application status</li>
    <li>Verify your identity and academic credentials</li>
    <li>Process payments</li>
</ul>

<h3>3. Data Protection</h3>
<p>We implement industry-standard security measures to protect your data, including encryption of sensitive fields and secure server infrastructure.</p>

<h3>4. Data Retention</h3>
<p>Your personal data is retained for the duration of your relationship with SKS Academy and as required by applicable laws and regulations.</p>

<h3>5. Your Rights</h3>
<p>You have the right to access, correct, or request deletion of your personal information by contacting our data protection officer.</p>

<h3>6. Contact</h3>
<p>For privacy-related inquiries, please contact us at info@sks.edu.</p>',
            ],
            [
                'title' => 'Contact Us',
                'slug' => 'contact',
                'is_system' => true,
                'content' => '<h2>Get in Touch</h2>
<p>We\'d love to hear from you. Whether you have questions about the admission process, need technical support, or want to learn more about our programs, our team is here to help.</p>

<h3>Office Hours</h3>
<p>Monday – Friday: 8:00 AM – 5:00 PM (EAT)<br>
Saturday: 9:00 AM – 1:00 PM<br>
Sunday & Public Holidays: Closed</p>

<h3>Admissions Office</h3>
<p>For application-related inquiries, please email admissions@sks.edu or call during office hours.</p>

<h3>Technical Support</h3>
<p>If you are experiencing issues with the portal, please email support@sks.edu with a description of the problem and screenshots if possible.</p>',
            ],
        ];

        foreach ($pages as $page) {
            SitePage::firstOrCreate(
                ['slug' => $page['slug']],
                $page
            );
        }
    }
}
