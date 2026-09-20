<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

/**
 * Defines the public/support copy that a Super Administrator may override.
 * Empty values deliberately fall back to the built-in, version-controlled copy.
 */
class ContentManagement extends BaseConfig
{
    /**
     * @return array<string, array{label:string, description:string, fields:array<string, array{label:string, type:string, placeholder:string}>}>
     */
    public static function groups(): array
    {
        return [
            'contact' => [
                'label'       => 'Contact Us',
                'description' => 'Public introduction shown above the contact form. Phone, email, and address remain in System Identity.',
                'fields'      => [
                    'content_contact_intro' => self::body('Introduction', 'Leave blank to use the built-in Contact Us introduction.'),
                ],
            ],
            'faq' => [
                'label'       => 'Frequently Asked Questions',
                'description' => 'Override individual questions or answers. The FAQ will continue to open one answer at a time.',
                'fields'      => self::pairedFields('content_faq', 7, 'Question', 'Answer'),
            ],
            'terms' => [
                'label'       => 'Terms of Service',
                'description' => 'Policy content. Saving a change automatically records a new last-updated date and audit-log entry.',
                'fields'      => self::pairedFields('content_terms', 8, 'Section title', 'Section text'),
            ],
            'commuter' => [
                'label'       => 'Commuter Help Guide',
                'description' => 'Public travel assistance shown to guest users.',
                'fields'      => array_merge([
                    'content_commuter_title' => self::title('Guide title', 'Commuter Help Guide & Travel Assistance'),
                    'content_commuter_intro' => self::body('Guide introduction', 'Leave blank to use the built-in introduction.'),
                ], self::pairedFields('content_commuter', 7, 'Topic title', 'Topic content')),
            ],
            'admin' => [
                'label'       => 'Administrator Help Guide',
                'description' => 'Operational guidance visible to Administrator accounts.',
                'fields'      => array_merge([
                    'content_admin_title' => self::title('Guide title', 'Administrator Help Guide'),
                ], self::pairedFields('content_admin', 10, 'Topic title', 'Topic content')),
            ],
            'superadmin' => [
                'label'       => 'Super Administrator Help Guide',
                'description' => 'A separate override of the administrator guide for Super Administrator accounts.',
                'fields'      => array_merge([
                    'content_superadmin_title' => self::title('Guide title', 'Super Administrator Help Guide'),
                ], self::pairedFields('content_superadmin', 10, 'Topic title', 'Topic content')),
            ],
            'dispatcher' => [
                'label'       => 'Dispatcher Help Guide',
                'description' => 'Queue and dispatch guidance visible to dispatcher accounts.',
                'fields'      => array_merge([
                    'content_dispatcher_title' => self::title('Guide title', 'Dispatcher Help Guide & Operational Procedures'),
                ], self::pairedFields('content_dispatcher', 9, 'Topic title', 'Topic content')),
            ],
        ];
    }

    /** @return array<string, array{label:string, type:string, placeholder:string}> */
    public static function fields(): array
    {
        $fields = [];
        foreach (self::groups() as $group) {
            $fields += $group['fields'];
        }

        return $fields;
    }

    /** @return array<string, array{label:string, type:string, placeholder:string}> */
    private static function pairedFields(string $prefix, int $count, string $titleLabel, string $bodyLabel): array
    {
        $fields = [];
        for ($i = 1; $i <= $count; $i++) {
            $fields["{$prefix}_{$i}_title"] = self::title("{$titleLabel} {$i}", 'Leave blank to keep the built-in title.');
            $fields["{$prefix}_{$i}_body"]  = self::body("{$bodyLabel} {$i}", 'Leave blank to keep the built-in content and formatting.');
        }

        return $fields;
    }

    /** @return array{label:string, type:string, placeholder:string} */
    private static function title(string $label, string $placeholder): array
    {
        return ['label' => $label, 'type' => 'text', 'placeholder' => $placeholder];
    }

    /** @return array{label:string, type:string, placeholder:string} */
    private static function body(string $label, string $placeholder): array
    {
        return ['label' => $label, 'type' => 'textarea', 'placeholder' => $placeholder];
    }
}
