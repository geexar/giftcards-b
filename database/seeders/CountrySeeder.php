<?php

namespace Database\Seeders;

use App\Models\Country;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CountrySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $countries = [
            // Arabic-speaking countries first
            [
                "name_en" => "Syria",
                "name_ar" => "سوريا",
                "code" => "SY",
                "dial_code" => "+963"
            ],
            [
                "name_en" => "United Arab Emirates",
                "name_ar" => "الإمارات العربية المتحدة",
                "code" => "AE",
                "dial_code" => "+971"
            ],
            [
                "name_en" => "Egypt",
                "name_ar" => "مصر",
                "code" => "EG",
                "dial_code" => "+20"
            ],
            [
                "name_en" => "Saudi Arabia",
                "name_ar" => "السعودية",
                "code" => "SA",
                "dial_code" => "+966"
            ],
            [
                "name_en" => "Bahrain",
                "name_ar" => "البحرين",
                "code" => "BH",
                "dial_code" => "+973"
            ],
            [
                "name_en" => "Palestine",
                "name_ar" => "فلسطين",
                "code" => "PS",
                "dial_code" => "+970"
            ],
            [
                "name_en" => "Jordan",
                "name_ar" => "الأردن",
                "code" => "JO",
                "dial_code" => "+962"
            ],
            [
                "name_en" => "Yemen",
                "name_ar" => "اليمن",
                "code" => "YE",
                "dial_code" => "+967"
            ],
            [
                "name_en" => "Kuwait",
                "name_ar" => "الكويت",
                "code" => "KW",
                "dial_code" => "+965"
            ],
            [
                "name_en" => "Qatar",
                "name_ar" => "قطر",
                "code" => "QA",
                "dial_code" => "+974"
            ],
            [
                "name_en" => "Oman",
                "name_ar" => "عمان",
                "code" => "OM",
                "dial_code" => "+968"
            ],
            [
                "name_en" => "Iraq",
                "name_ar" => "العراق",
                "code" => "IQ",
                "dial_code" => "+964"
            ],
            [
                "name_en" => "Lebanon",
                "name_ar" => "لبنان",
                "code" => "LB",
                "dial_code" => "+961"
            ],
            [
                "name_en" => "Sudan",
                "name_ar" => "السودان",
                "code" => "SD",
                "dial_code" => "+249"
            ],
            [
                "name_en" => "Libya",
                "name_ar" => "ليبيا",
                "code" => "LY",
                "dial_code" => "+218"
            ],
            [
                "name_en" => "Tunisia",
                "name_ar" => "تونس",
                "code" => "TN",
                "dial_code" => "+216"
            ],
            [
                "name_en" => "Algeria",
                "name_ar" => "الجزائر",
                "code" => "DZ",
                "dial_code" => "+213"
            ],
            [
                "name_en" => "Morocco",
                "name_ar" => "المغرب",
                "code" => "MA",
                "dial_code" => "+212"
            ],
            [
                "name_en" => "Mauritania",
                "name_ar" => "موريتانيا",
                "code" => "MR",
                "dial_code" => "+222"
            ],
            [
                "name_en" => "Somalia",
                "name_ar" => "الصومال",
                "code" => "SO",
                "dial_code" => "+252"
            ],
            [
                "name_en" => "Comoros",
                "name_ar" => "جزر القمر",
                "code" => "KM",
                "dial_code" => "+269"
            ],
            [
                "name_en" => "Djibouti",
                "name_ar" => "جيبوتي",
                "code" => "DJ",
                "dial_code" => "+253"
            ],
            // Other commonly used countries
            [
                "name_en" => "United States",
                "name_ar" => "الولايات المتحدة",
                "code" => "US",
                "dial_code" => "+1"
            ],
            [
                "name_en" => "United Kingdom",
                "name_ar" => "المملكة المتحدة",
                "code" => "GB",
                "dial_code" => "+44"
            ],
            [
                "name_en" => "Canada",
                "name_ar" => "كندا",
                "code" => "CA",
                "dial_code" => "+1"
            ],
            [
                "name_en" => "Australia",
                "name_ar" => "أستراليا",
                "code" => "AU",
                "dial_code" => "+61"
            ],
            [
                "name_en" => "Germany",
                "name_ar" => "ألمانيا",
                "code" => "DE",
                "dial_code" => "+49"
            ],
            [
                "name_en" => "France",
                "name_ar" => "فرنسا",
                "code" => "FR",
                "dial_code" => "+33"
            ],
            [
                "name_en" => "Japan",
                "name_ar" => "اليابان",
                "code" => "JP",
                "dial_code" => "+81"
            ],
            [
                "name_en" => "China",
                "name_ar" => "الصين",
                "code" => "CN",
                "dial_code" => "+86"
            ],
            [
                "name_en" => "India",
                "name_ar" => "الهند",
                "code" => "IN",
                "dial_code" => "+91"
            ],
            [
                "name_en" => "Brazil",
                "name_ar" => "البرازيل",
                "code" => "BR",
                "dial_code" => "+55"
            ],
            [
                "name_en" => "South Korea",
                "name_ar" => "كوريا الجنوبية",
                "code" => "KR",
                "dial_code" => "+82"
            ],
            [
                "name_en" => "Russia",
                "name_ar" => "روسيا",
                "code" => "RU",
                "dial_code" => "+7"
            ],
            [
                "name_en" => "Mexico",
                "name_ar" => "المسكيك",
                "code" => "MX",
                "dial_code" => "+52"
            ],
            [
                "name_en" => "Italy",
                "name_ar" => "إيطاليا",
                "code" => "IT",
                "dial_code" => "+39"
            ],
            [
                "name_en" => "Spain",
                "name_ar" => "إسبانيا",
                "code" => "ES",
                "dial_code" => "+34"
            ],
            [
                "name_en" => "South Africa",
                "name_ar" => "جنوب أفريقيا",
                "code" => "ZA",
                "dial_code" => "+27"
            ],
            [
                "name_en" => "Nigeria",
                "name_ar" => "نيجيريا",
                "code" => "NG",
                "dial_code" => "+234"
            ],
            [
                "name_en" => "Turkey",
                "name_ar" => "تركيا",
                "code" => "TR",
                "dial_code" => "+90"
            ],
            [
                "name_en" => "Singapore",
                "name_ar" => "سنغافورة",
                "code" => "SG",
                "dial_code" => "+65"
            ],
            [
                "name_en" => "Malaysia",
                "name_ar" => "ماليزيا",
                "code" => "MY",
                "dial_code" => "+60"
            ],
            [
                "name_en" => "Indonesia",
                "name_ar" => "إندونيسيا",
                "code" => "ID",
                "dial_code" => "+62"
            ],
            [
                "name_en" => "Vietnam",
                "name_ar" => "فيتنام",
                "code" => "VN",
                "dial_code" => "+84"
            ],
            [
                "name_en" => "Philippines",
                "name_ar" => "الفلبين",
                "code" => "PH",
                "dial_code" => "+63"
            ],
            [
                "name_en" => "Thailand",
                "name_ar" => "تايلند",
                "code" => "TH",
                "dial_code" => "+66"
            ],
            [
                "name_en" => "Pakistan",
                "name_ar" => "باكستان",
                "code" => "PK",
                "dial_code" => "+92"
            ],
            [
                "name_en" => "Bangladesh",
                "name_ar" => "بنغلاديش",
                "code" => "BD",
                "dial_code" => "+880"
            ],
            [
                "name_en" => "Argentina",
                "name_ar" => "الأرجنتين",
                "code" => "AR",
                "dial_code" => "+54"
            ],
            [
                "name_en" => "Colombia",
                "name_ar" => "كولومبيا",
                "code" => "CO",
                "dial_code" => "+57"
            ],
            [
                "name_en" => "Chile",
                "name_ar" => "تشيلي",
                "code" => "CL",
                "dial_code" => "+56"
            ],
            [
                "name_en" => "Sweden",
                "name_ar" => "السويد",
                "code" => "SE",
                "dial_code" => "+46"
            ],
            [
                "name_en" => "Netherlands",
                "name_ar" => "هولندا",
                "code" => "NL",
                "dial_code" => "+31"
            ],
            [
                "name_en" => "Belgium",
                "name_ar" => "بلجيكا",
                "code" => "BE",
                "dial_code" => "+32"
            ],
            [
                "name_en" => "Switzerland",
                "name_ar" => "سويسرا",
                "code" => "CH",
                "dial_code" => "+41"
            ],
            [
                "name_en" => "Austria",
                "name_ar" => "النمسا",
                "code" => "AT",
                "dial_code" => "+43"
            ],
            [
                "name_en" => "Poland",
                "name_ar" => "بولندا",
                "code" => "PL",
                "dial_code" => "+48"
            ],
            [
                "name_en" => "Greece",
                "name_ar" => "اليونان",
                "code" => "GR",
                "dial_code" => "+30"
            ],
            [
                "name_en" => "Portugal",
                "name_ar" => "البرتغال",
                "code" => "PT",
                "dial_code" => "+351"
            ],
            [
                "name_en" => "New Zealand",
                "name_ar" => "نيوزيلندا",
                "code" => "NZ",
                "dial_code" => "+64"
            ],
            [
                "name_en" => "Ireland",
                "name_ar" => "أيرلندا",
                "code" => "IE",
                "dial_code" => "+353"
            ],
            [
                "name_en" => "Denmark",
                "name_ar" => "الدنمارك",
                "code" => "DK",
                "dial_code" => "+45"
            ],
            [
                "name_en" => "Norway",
                "name_ar" => "النرويج",
                "code" => "NO",
                "dial_code" => "+47"
            ],
            [
                "name_en" => "Finland",
                "name_ar" => "فنلندا",
                "code" => "FI",
                "dial_code" => "+358"
            ],
            [
                "name_en" => "Ukraine",
                "name_ar" => "أوكرانيا",
                "code" => "UA",
                "dial_code" => "+380"
            ],
            [
                "name_en" => "Romania",
                "name_ar" => "رومانيا",
                "code" => "RO",
                "dial_code" => "+40"
            ],
            [
                "name_en" => "Hungary",
                "name_ar" => "المجر",
                "code" => "HU",
                "dial_code" => "+36"
            ],
            [
                "name_en" => "Czechia",
                "name_ar" => "التشيك",
                "code" => "CZ",
                "dial_code" => "+420"
            ],
            [
                "name_en" => "Slovakia",
                "name_ar" => "سلوفاكيا",
                "code" => "SK",
                "dial_code" => "+421"
            ],
            [
                "name_en" => "Bulgaria",
                "name_ar" => "بلغاريا",
                "code" => "BG",
                "dial_code" => "+359"
            ],
            [
                "name_en" => "Croatia",
                "name_ar" => "كرواتيا",
                "code" => "HR",
                "dial_code" => "+385"
            ],
            [
                "name_en" => "Serbia",
                "name_ar" => "صيربيا",
                "code" => "RS",
                "dial_code" => "+381"
            ],
            [
                "name_en" => "Kenya",
                "name_ar" => "كينيا",
                "code" => "KE",
                "dial_code" => "+254"
            ],
            [
                "name_en" => "Ghana",
                "name_ar" => "غانا",
                "code" => "GH",
                "dial_code" => "+233"
            ],
            [
                "name_en" => "Ethiopia",
                "name_ar" => "إثيوبيا",
                "code" => "ET",
                "dial_code" => "+251"
            ],
            [
                "name_en" => "Peru",
                "name_ar" => "بيرو",
                "code" => "PE",
                "dial_code" => "+51"
            ],
            [
                "name_en" => "Ecuador",
                "name_ar" => "الإكوادور",
                "code" => "EC",
                "dial_code" => "+593"
            ],
            [
                "name_en" => "Uruguay",
                "name_ar" => "الأوروغواي",
                "code" => "UY",
                "dial_code" => "+598"
            ],
            [
                "name_en" => "Paraguay",
                "name_ar" => "باراغواي",
                "code" => "PY",
                "dial_code" => "+595"
            ],
            [
                "name_en" => "Costa Rica",
                "name_ar" => "كوستاريكا",
                "code" => "CR",
                "dial_code" => "+506"
            ],
            [
                "name_en" => "Taiwan",
                "name_ar" => "تايوان",
                "code" => "TW",
                "dial_code" => "+886"
            ],
            [
                "name_en" => "Hong Kong",
                "name_ar" => "هونغ كونغ",
                "code" => "HK",
                "dial_code" => "+852"
            ],
            [
                "name_en" => "Albania",
                "name_ar" => "ألبانيا",
                "code" => "AL",
                "dial_code" => "+355"
            ],
            [
                "name_en" => "Angola",
                "name_ar" => "جمهورية أنغولا",
                "code" => "AO",
                "dial_code" => "+244"
            ],
            [
                "name_en" => "Armenia",
                "name_ar" => "أرمينيا",
                "code" => "AM",
                "dial_code" => "+374"
            ],
            [
                "name_en" => "Azerbaijan",
                "name_ar" => "أذربيجان",
                "code" => "AZ",
                "dial_code" => "+994"
            ],
            [
                "name_en" => "Belarus",
                "name_ar" => "بيلاروسيا",
                "code" => "BY",
                "dial_code" => "+375"
            ],
            [
                "name_en" => "Bosnia and Herzegovina",
                "name_ar" => "البوسنة والهرسك",
                "code" => "BA",
                "dial_code" => "+387"
            ],
            [
                "name_en" => "Botswana",
                "name_ar" => "بوتسوانا",
                "code" => "BW",
                "dial_code" => "+267"
            ],
            [
                "name_en" => "Brunei",
                "name_ar" => "بروناي",
                "code" => "BN",
                "dial_code" => "+673"
            ],
            [
                "name_en" => "Cambodia",
                "name_ar" => "كمبوديا",
                "code" => "KH",
                "dial_code" => "+855"
            ],
            [
                "name_en" => "Cameroon",
                "name_ar" => "الكاميرون",
                "code" => "CM",
                "dial_code" => "+237"
            ],
            [
                "name_en" => "Cuba",
                "name_ar" => "كوبا",
                "code" => "CU",
                "dial_code" => "+53"
            ],
            [
                "name_en" => "Cyprus",
                "name_ar" => "قبرص",
                "code" => "CY",
                "dial_code" => "+357"
            ],
            [
                "name_en" => "Dominican Republic",
                "name_ar" => "جمهورية الدومينيكان",
                "code" => "DO",
                "dial_code" => "+1"
            ],
            [
                "name_en" => "Estonia",
                "name_ar" => "إستونيا",
                "code" => "EE",
                "dial_code" => "+372"
            ],
            [
                "name_en" => "Georgia",
                "name_ar" => "جورجيا",
                "code" => "GE",
                "dial_code" => "+995"
            ],
            [
                "name_en" => "Guatemala",
                "name_ar" => "غواتيمالا",
                "code" => "GT",
                "dial_code" => "+502"
            ],
            [
                "name_en" => "Honduras",
                "name_ar" => "هندوراس",
                "code" => "HN",
                "dial_code" => "+504"
            ],
            [
                "name_en" => "Iceland",
                "name_ar" => "آيسلندا",
                "code" => "IS",
                "dial_code" => "+354"
            ],
            [
                "name_en" => "Iran",
                "name_ar" => "إيران",
                "code" => "IR",
                "dial_code" => "+98"
            ],
            [
                "name_en" => "Kazakhstan",
                "name_ar" => "كازاخستان",
                "code" => "KZ",
                "dial_code" => "+7"
            ],
            [
                "name_en" => "Kyrgyzstan",
                "name_ar" => "قيرغيزستان",
                "code" => "KG",
                "dial_code" => "+996"
            ],
            [
                "name_en" => "Latvia",
                "name_ar" => "لاتفيا",
                "code" => "LV",
                "dial_code" => "+371"
            ],
            [
                "name_en" => "Lithuania",
                "name_ar" => "ليتوانيا",
                "code" => "LT",
                "dial_code" => "+370"
            ],
            [
                "name_en" => "Luxembourg",
                "name_ar" => "لوكسمبورغ",
                "code" => "LU",
                "dial_code" => "+352"
            ],
            [
                "name_en" => "Malta",
                "name_ar" => "مالطا",
                "code" => "MT",
                "dial_code" => "+356"
            ],
            [
                "name_en" => "Mongolia",
                "name_ar" => "منغوليا",
                "code" => "MN",
                "dial_code" => "+976"
            ],
            [
                "name_en" => "Nepal",
                "name_ar" => "نيبال",
                "code" => "NP",
                "dial_code" => "+977"
            ],
            [
                "name_en" => "Sri Lanka",
                "name_ar" => "سريلانكا",
                "code" => "LK",
                "dial_code" => "+94"
            ],
            [
                "name_en" => "Tanzania",
                "name_ar" => "تنزانيا",
                "code" => "TZ",
                "dial_code" => "+255"
            ],
            [
                "name_en" => "Uganda",
                "name_ar" => "أوغندا",
                "code" => "UG",
                "dial_code" => "+256"
            ],
            [
                "name_en" => "Uzbekistan",
                "name_ar" => "أوزباكستان",
                "code" => "UZ",
                "dial_code" => "+998"
            ],
            [
                "name_en" => "Venezuela",
                "name_ar" => "فنزويلا",
                "code" => "VE",
                "dial_code" => "+58"
            ],
            [
                "name_en" => "Zambia",
                "name_ar" => "زامبيا",
                "code" => "ZM",
                "dial_code" => "+260"
            ],
            [
                "name_en" => "Zimbabwe",
                "name_ar" => "زيمبابوي",
                "code" => "ZW",
                "dial_code" => "+263"
            ]
        ];

        foreach ($countries as $country) {
            Country::create([
                'code' => $country['code'],
                'dial_code' => $country['dial_code'],
                'name' => [
                    'ar' => $country['name_ar'],
                    'en' => $country['name_en'],
                ],
            ]);
        }
    }
}