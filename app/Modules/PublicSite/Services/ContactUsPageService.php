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
