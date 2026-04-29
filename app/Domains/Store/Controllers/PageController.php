<?php

namespace App\Domains\Store\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PageController extends Controller
{
    public function about()
    {
        $data = [
            'title' => 'Victory of Wellness',
            'brand_name' => 'Bionic',
            'slogan' => 'Victory of Wellness',
            'description' => 'Bionic is our nature-powered wellness brand, created to make toxin-free, functional foods accessible without compromising on purity or design. Every product is crafted to nourish body and mind while honoring the planet.',
            'values_tags' => ['Affordable premium', 'Toxin-free', 'Nature-driven'],
            'offerings' => [
                [
                    'title' => 'Pure Foods & Essentials',
                    'items' => 'Honey, Seeds, Nuts, Dry Fish, Cold-Pressed Oils, Ghee, Botanicals, Functional Powders.',
                    'icon' => 'fa-solid fa-leaf'
                ],
                [
                    'title' => 'Safety & Trust',
                    'items' => 'Zero Toxic Additives; Batch-wise Quality Checks; Transparent Sourcing.',
                    'icon' => 'fa-solid fa-shield-halved'
                ],
                [
                    'title' => 'Customer Experience',
                    'items' => 'Clean Design, Recyclable Packaging, Subscriptions, and Community Programs.',
                    'icon' => 'fa-solid fa-face-smile'
                ],
                [
                    'title' => 'Multi-Channel Presence',
                    'items' => 'Online (Bionic.garden), Retail Partners, and Curated Pop-ups.',
                    'icon' => 'fa-solid fa-store'
                ]
            ],
            'pillars' => ['Purity', 'Performance', 'Transparency', 'Sustainability', 'Everyday Accessibility'],
            'parent_brand' => [
                'name' => 'Bôr dé Güna',
                'founded' => '2019',
                'vision' => 'Bor de Guna was founded in 2019 with a vision to create a better, healthier, and more harmonious world. We are more than a company—we are a house of purpose-driven brands that embody purity, wellness, and sustainability.',
                'mission' => 'From premium natural foods to luxury artisanal products, Bor de Guna is dedicated to offering toxin-free, nature-inspired solutions that transform lives. With innovation, integrity, and deep respect for people and the planet, we aim to set new benchmarks in health, lifestyle, and responsible business.'
            ],
            'founder' => [
                'name' => 'Chowdhury Mohammad Moin',
                'short_name' => 'CM Moin',
                'designation' => 'Clinical Certified Nutritionist, Founder & Growth Mentor',
                'bio' => 'CM Moin is a clinical Certified Nutritionist, motivational speaker, businessman, founder, teacher, content creator, and growth mentor. His leadership is grounded in the intersection of Wellness & Spirituality.',
                'image' => '', // A more professional, luxurious placeholder
                'expertise' => ['Leadership', 'Wellness', 'Spirituality']
            ],
            'gallery_link' => route('gallery'),
            'videos' => [
                [
                    'title' => 'The Journey of Bionic',
                    'badge' => 'Documentary',
                    'thumbnail' => 'https://images.unsplash.com/photo-1505935428862-770b6f24f629?auto=format&fit=crop&q=80&w=600',
                    'type' => 'youtube',
                    'src' => 'kc6Fl8U384g' // Placeholder YouTube ID
                ],
                [
                    'title' => 'Purity in Every Drop',
                    'badge' => 'Behind the Scenes',
                    'thumbnail' => 'https://images.unsplash.com/photo-1473448912268-2022ce9509d8?auto=format&fit=crop&q=80&w=600',
                    'type' => 'youtube',
                    'src' => 'kc6Fl8U384g'
                ],
                [
                    'title' => 'Sustainable Harvesting',
                    'badge' => 'Environment',
                    'thumbnail' => 'https://images.unsplash.com/photo-1501854140801-50d01698950b?auto=format&fit=crop&q=80&w=600',
                    'type' => 'youtube',
                    'src' => 'kc6Fl8U384g'
                ],
                [
                    'title' => 'A Message from CM Moin',
                    'badge' => 'Leadership',
                    'thumbnail' => 'https://images.unsplash.com/photo-1557804506-669a67965ba0?auto=format&fit=crop&q=80&w=600',
                    'type' => 'youtube',
                    'src' => 'kc6Fl8U384g'
                ],
                [
                    'title' => 'Our Honey Extraction',
                    'badge' => 'Process',
                    'thumbnail' => 'https://images.unsplash.com/photo-1587049352847-81a56d773cac?auto=format&fit=crop&q=80&w=600',
                    'type' => 'youtube',
                    'src' => 'kc6Fl8U384g'
                ],
                [
                    'title' => 'The Bor de Guna Vision',
                    'badge' => 'Heritage',
                    'thumbnail' => 'https://images.unsplash.com/photo-1522202176988-66273c2fd55f?auto=format&fit=crop&q=80&w=600',
                    'type' => 'youtube',
                    'src' => 'kc6Fl8U384g'
                ],
                [
                    'title' => 'Customer Testimonials',
                    'badge' => 'Community',
                    'thumbnail' => 'https://images.unsplash.com/photo-1517245386807-bb43f82c33c4?auto=format&fit=crop&q=80&w=600',
                    'type' => 'youtube',
                    'src' => 'kc6Fl8U384g'
                ],
                [
                    'title' => 'Wellness and You',
                    'badge' => 'Health',
                    'thumbnail' => 'https://images.unsplash.com/photo-1544367567-0f2fcb009e0b?auto=format&fit=crop&q=80&w=600',
                    'type' => 'youtube',
                    'src' => 'kc6Fl8U384g'
                ],
                [
                    'title' => 'The Future of Nutrition',
                    'badge' => 'Innovation',
                    'thumbnail' => 'https://images.unsplash.com/photo-1490818387583-1baba5e638af?auto=format&fit=crop&q=80&w=600',
                    'type' => 'youtube',
                    'src' => 'kc6Fl8U384g'
                ]
            ]
        ];
        return view('store.pages.about', compact('data'));
    }

    public function contact()
    {
        $data = [
            'title' => 'Contact Us',
            'subtitle' => 'We are here to help you on your health journey',
            'email' => 'care@bionic.garden',
            'phone' => '+8801733358158',
            'address' => '65, Feroza Garden, Shahid Smriti Sarak, Barguna-8700',
            'map_embed' => 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d7390.790966773862!2d90.12808499696692!3d22.149013013259335!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x30aa998d2befec69%3A0x98060f6912c647bd!2sSANGRAM%20(Sangathita%20Gramunnyan%20Karmasuchi)!5e0!3m2!1sen!2sbd!4v1777365377648!5m2!1sen!2sbd'
        ];
        return view('store.pages.contact', compact('data'));
    }

    public function faq()
    {
        $data = [
            'title' => 'Frequently Asked Questions',
            'subtitle' => 'Everything you need to know about our products and services',
            'categories' => [
                [
                    'name' => 'Ordering & Delivery',
                    'items' => [
                        ['q' => 'How do I place an order?', 'a' => 'You can easily place an order by selecting products, adding them to your cart, and following the checkout process.'],
                        ['q' => 'How long does delivery take?', 'a' => 'Inside Dhaka, it takes 24-48 hours. Outside Dhaka, it takes 3-5 business days.'],
                    ]
                ],
                [
                    'name' => 'Product Quality',
                    'items' => [
                        ['q' => 'Are your products 100% organic?', 'a' => 'Yes, we source only certified organic products or high-quality natural items from trusted farms.'],
                        ['q' => 'What is the shelf life of your items?', 'a' => 'Shelf life varies by product and is clearly mentioned on the packaging.'],
                    ]
                ]
            ]
        ];
        return view('store.pages.faq', compact('data'));
    }

    public function privacy()
    {
        $data = [
            'title' => 'Privacy Policy',
            'last_updated' => 'Last Updated: April 28, 2026',
            'sections' => [
                [
                    'heading' => 'Information Collection',
                    'content' => 'We collect information you provide directly to us, such as when you create an account, place an order, or contact us for support.'
                ],
                [
                    'heading' => 'How We Use Your Information',
                    'content' => 'We use your information to process transactions, send order updates, and improve our website experience.'
                ],
                [
                    'heading' => 'Data Protection',
                    'content' => 'We implement high-level security measures, including SSL encryption, to protect your personal and financial data.'
                ]
            ]
        ];
        return view('store.pages.privacy', compact('data'));
    }

    public function terms()
    {
        $data = [
            'title' => 'Terms & Conditions',
            'last_updated' => 'Last Updated: April 28, 2026',
            'sections' => [
                [
                    'heading' => 'Account Terms',
                    'content' => 'By using this site, you agree to provide accurate information and maintain the security of your account credentials.'
                ],
                [
                    'heading' => 'Payment & Refunds',
                    'content' => 'All payments are processed securely. Refunds are handled according to our refund policy, typically within 7-10 business days for valid claims.'
                ],
                [
                    'heading' => 'Intellectual Property',
                    'content' => 'All content on this website, including text, images, and logos, is the property of Bionic Project.'
                ]
            ]
        ];
        return view('store.pages.terms', compact('data'));
    }

    public function disclaimer()
    {
        $data = [
            'title' => 'Legal Disclaimer',
            'last_updated' => 'Last Updated: April 28, 2026',
            'sections' => [
                [
                    'heading' => 'Health Disclaimer',
                    'content' => 'The information on this website is for informational purposes only and is not intended as a substitute for professional medical advice, diagnosis, or treatment.'
                ],
                [
                    'heading' => 'Product Representation',
                    'content' => 'While we strive to provide accurate product information, actual product packaging and materials may contain more or different information than shown.'
                ],
                [
                    'heading' => 'External Links',
                    'content' => 'Bionic Project is not responsible for the content or accuracy of third-party websites linked from our store.'
                ]
            ]
        ];
        return view('store.pages.terms', compact('data')); // Reusing the terms view for layout consistency
    }

    public function blog()
    {
        $data = [
            'title' => 'Bionic Health Blog',
            'subtitle' => 'Latest insights on nutrition, wellness, and organic living',
            'posts' => [
                [
                    'title' => '5 Superfoods You Need in Your Diet',
                    'excerpt' => 'Discover the most nutrient-dense foods that can transform your health and energy levels.',
                    'author' => 'Dr. Sarah Health',
                    'date' => 'April 25, 2026',
                    'image' => 'https://images.unsplash.com/photo-1512621776951-a57141f2eefd?auto=format&fit=crop&q=80&w=600',
                    'category' => 'Nutrition'
                ],
                [
                    'title' => 'The Science of Raw Honey',
                    'excerpt' => 'Why raw honey is more than just a sweetener. Learn about its antibacterial and healing properties.',
                    'author' => 'Organic Farmer John',
                    'date' => 'April 22, 2026',
                    'image' => 'https://images.unsplash.com/photo-1589182373726-e4f658ab50f0?auto=format&fit=crop&q=80&w=600',
                    'category' => 'Superfoods'
                ],
                [
                    'title' => 'How to Detox Naturally',
                    'excerpt' => 'Simple daily habits that can help your body cleanse itself without extreme diets.',
                    'author' => 'Wellness Expert',
                    'date' => 'April 18, 2026',
                    'image' => 'https://images.unsplash.com/photo-1544367567-0f2fcb009e0b?auto=format&fit=crop&q=80&w=600',
                    'category' => 'Wellness'
                ]
            ]
        ];
        return view('store.blogs.index', compact('data'));
    }

    public function gallery()
    {
        $data = [
            'title' => 'The Gallery',
            'subtitle' => 'A curated collection of imagery and visual documentaries.',
            'items' => [
                [
                    'type' => 'image',
                    'src' => 'https://images.unsplash.com/photo-1542601906990-b4d3fb778b09?auto=format&fit=crop&q=80&w=800',
                    'title' => 'The Natural Origins',
                    'badge' => 'Origin'
                ],
                [
                    'type' => 'video',
                    'src' => 'kc6Fl8U384g',
                    'thumbnail' => 'https://images.unsplash.com/photo-1505935428862-770b6f24f629?auto=format&fit=crop&q=80&w=800',
                    'title' => 'The Journey of Bionic',
                    'badge' => 'Documentary'
                ],
                [
                    'type' => 'image',
                    'src' => 'https://images.unsplash.com/photo-1522202176988-66273c2fd55f?auto=format&fit=crop&q=80&w=800',
                    'title' => 'Bor de Guna Vision',
                    'badge' => 'Heritage'
                ],
                [
                    'type' => 'image',
                    'src' => 'https://images.unsplash.com/photo-1473448912268-2022ce9509d8?auto=format&fit=crop&q=80&w=800',
                    'title' => 'Purity in Every Drop',
                    'badge' => 'Process'
                ],
                [
                    'type' => 'video',
                    'src' => 'kc6Fl8U384g',
                    'thumbnail' => 'https://images.unsplash.com/photo-1557804506-669a67965ba0?auto=format&fit=crop&q=80&w=800',
                    'title' => 'A Message from CM Moin',
                    'badge' => 'Leadership'
                ],
                [
                    'type' => 'image',
                    'src' => 'https://images.unsplash.com/photo-1587049352847-81a56d773cac?auto=format&fit=crop&q=80&w=800',
                    'title' => 'Honey Extraction',
                    'badge' => 'Process'
                ]
            ]
        ];
        return view('store.pages.gallery', compact('data'));
    }
}
