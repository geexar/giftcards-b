<?php

namespace Database\Seeders;

use App\Models\Faq;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class FaqSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faqs = [
            [
                'question' => [
                    'en' => 'What is your service about?',
                    'ar' => 'ما هو موضوع خدمتكم؟'
                ],
                'answer' => [
                    'en' => 'Our service provides innovative solutions for your needs.',
                    'ar' => 'خدمتنا توفر حلولاً مبتكرة لاحتياجاتك.'
                ]
            ],
            [
                'question' => [
                    'en' => 'How can I contact support?',
                    'ar' => 'كيف يمكنني التواصل مع الدعم؟'
                ],
                'answer' => [
                    'en' => 'You can reach our support team via email or phone 24/7.',
                    'ar' => 'يمكنك التواصل مع فريق الدعم عبر البريد الإلكتروني أو الهاتف على مدار الساعة.'
                ]
            ],
            [
                'question' => [
                    'en' => 'What are the payment options?',
                    'ar' => 'ما هي خيارات الدفع؟'
                ],
                'answer' => [
                    'en' => 'We accept credit cards, PayPal, and bank transfers.',
                    'ar' => 'نقبل بطاقات الائتمان، باي بال، والتحويلات البنكية.'
                ]
            ],
            [
                'question' => [
                    'en' => 'Is there a free trial available?',
                    'ar' => 'هل يوجد تجربة مجانية متاحة؟'
                ],
                'answer' => [
                    'en' => 'Yes, we offer a 14-day free trial for new users.',
                    'ar' => 'نعم، نحن نقدم تجربة مجانية لمدة 14 يومًا للمستخدمين الجدد.'
                ]
            ],
            [
                'question' => [
                    'en' => 'How secure is my data?',
                    'ar' => 'ما مدى أمان بياناتي؟'
                ],
                'answer' => [
                    'en' => 'We use industry-standard encryption to protect your data.',
                    'ar' => 'نستخدم التشفير القياسي في الصناعة لحماية بياناتك.'
                ]
            ],
            [
                'question' => [
                    'en' => 'Can I cancel my subscription?',
                    'ar' => 'هل يمكنني إلغاء اشتراكي؟'
                ],
                'answer' => [
                    'en' => 'Yes, you can cancel anytime from your account settings.',
                    'ar' => 'نعم، يمكنك الإلغاء في أي وقت من إعدادات حسابك.'
                ]
            ],
            [
                'question' => [
                    'en' => 'What are your operating hours?',
                    'ar' => 'ما هي ساعات عملكم؟'
                ],
                'answer' => [
                    'en' => 'We operate Monday to Friday, 9 AM to 5 PM.',
                    'ar' => 'نعمل من الإثنين إلى الجمعة، من 9 صباحًا إلى 5 مساءً.'
                ]
            ],
            [
                'question' => [
                    'en' => 'Do you offer refunds?',
                    'ar' => 'هل تقدمون استرداد الأموال؟'
                ],
                'answer' => [
                    'en' => 'Refunds are available within 30 days of purchase.',
                    'ar' => 'الاسترداد متاح خلال 30 يومًا من الشراء.'
                ]
            ],
            [
                'question' => [
                    'en' => 'How do I update my account details?',
                    'ar' => 'كيف يمكنني تحديث تفاصيل حسابي؟'
                ],
                'answer' => [
                    'en' => 'You can update your details in the profile section.',
                    'ar' => 'يمكنك تحديث تفاصيلك في قسم الملف الشخصي.'
                ]
            ],
            [
                'question' => [
                    'en' => 'Are there any discounts for students?',
                    'ar' => 'هل يوجد خصومات للطلاب؟'
                ],
                'answer' => [
                    'en' => 'Yes, we offer a 20% discount for verified students.',
                    'ar' => 'نعم، نقدم خصمًا بنسبة 20% للطلاب الموثقين.'
                ]
            ]
        ];

        foreach ($faqs as $faq) {
            Faq::create([
                'question' => $faq['question'],
                'answer' => $faq['answer']
            ]);
        }
    }
}
