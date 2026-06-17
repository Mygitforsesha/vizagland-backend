<?php

namespace App\Modules\PublicSite\Services;

class ContactUsPageService
{
    /**
     * @return array<string, mixed>
     */
    public function getPageContent(): array
    {
        return [
            'contact_us_helpline' => [
                'contact_us_helpline_phone' => '1234567989',
                'contact_us_helpline_phone_note' => 'Toll Free • Mon–Fri 9AM–5:30PM',
                'contact_us_helpline_email' => 'support@aprealestate.ap.gov.in',
                'contact_us_helpline_email_note' => 'Response within 24 hours',
                'contact_us_helpline_address' => 'Vizag Land Office, Visakhapatnam - 530003',
            ],
            'contact_us_district_offices' => [
                [
                    'contact_us_district_office_city' => 'Hyderabad',
                    'contact_us_district_office_phone' => '040-23456789',
                ],
                [
                    'contact_us_district_office_city' => 'Vijayawada',
                    'contact_us_district_office_phone' => '0866-2345678',
                ],
                [
                    'contact_us_district_office_city' => 'Visakhapatnam',
                    'contact_us_district_office_phone' => '0891-2345678',
                ],
                [
                    'contact_us_district_office_city' => 'Tirupati',
                    'contact_us_district_office_phone' => '0877-2345678',
                ],
                [
                    'contact_us_district_office_city' => 'Guntur',
                    'contact_us_district_office_phone' => '0863-2345678',
                ],
                [
                    'contact_us_district_office_city' => 'Nellore',
                    'contact_us_district_office_phone' => '0861-2345678',
                ],
            ],
            'contact_us_working_hours' => [
                [
                    'contact_us_working_hours_day' => 'Mon - Fri',
                    'contact_us_working_hours_time' => '9:00 AM - 5:30 PM',
                ],
                [
                    'contact_us_working_hours_day' => 'Saturday',
                    'contact_us_working_hours_time' => '9:00 AM - 1:00 PM',
                ],
                [
                    'contact_us_working_hours_day' => 'Sunday & Holidays',
                    'contact_us_working_hours_time' => 'Closed',
                ],
            ],
            'contact_us_faqs' => [
                [
                    'contact_us_faq_question' => 'How do I verify if a property is genuine?',
                    'contact_us_faq_answer' => 'Check the property reference ID on the portal, review approved listing documents, and contact the district office helpline for verification support.',
                ],
                [
                    'contact_us_faq_question' => 'How can an agent register on the portal?',
                    'contact_us_faq_answer' => 'Click Register on the homepage, choose the agent role, complete your profile, and submit the required documents for admin approval.',
                ],
                [
                    'contact_us_faq_question' => 'Is there a fee to list properties on this portal?',
                    'contact_us_faq_answer' => 'Basic property listings are free. Premium membership plans may offer additional visibility and lead management features.',
                ],
            ],
        ];
    }
}
