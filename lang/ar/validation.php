<?php

use Illuminate\Support\Facades\Lang;

return [

    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines contain the default error messages used by
    | the validator class. Some of these rules have multiple versions such
    | as the size rules. Feel free to tweak each of these messages here.
    |
    */

    'accepted' => 'يجب قبول حقل :attribute.',
    'accepted_if' => 'يجب قبول حقل :attribute عندما يكون :other هو :value.',
    'active_url' => 'حقل :attribute ليس رابطًا صالحًا.',
    'after' => 'يجب أن يكون حقل :attribute تاريخًا بعد :date.',
    'after_or_equal' => 'يجب أن يكون حقل :attribute تاريخًا بعد أو يساوي :date.',
    'alpha' => 'يجب أن يحتوي حقل :attribute على أحرف فقط.',
    'alpha_dash' => 'يجب أن يحتوي حقل :attribute على أحرف، أرقام، شرطات، وشرطات سفلية فقط.',
    'alpha_num' => 'يجب أن يحتوي حقل :attribute على أحرف وأرقام فقط.',
    'array' => 'يجب أن يكون حقل :attribute مصفوفة.',
    'ascii' => 'يجب أن يحتوي حقل :attribute على أحرف وأرقام أحادية البايت فقط.',
    'before' => 'يجب أن يكون حقل :attribute تاريخًا قبل :date.',
    'before_or_equal' => 'يجب أن يكون حقل :attribute تاريخًا قبل أو يساوي :date.',
    'between' => [
        'array' => 'يجب أن يحتوي حقل :attribute على عناصر بين :min و :max.',
        'file' => 'يجب أن يكون حجم حقل :attribute بين :min و :max كيلوبايت.',
        'numeric' => 'يجب أن تكون قيمة حقل :attribute بين :min و :max.',
        'string' => 'يجب أن يكون طول حقل :attribute بين :min و :max حروف.',
    ],
    'boolean' => 'يجب أن يكون حقل :attribute 1 أو 0',
    'can' => 'حقل :attribute يحتوي على قيمة غير مسموح بها.',
    'confirmed' => 'حقل التأكيد لا يتطابق مع كلمة المرور',
    'contains' => 'حقل :attribute يفتقد إلى قيمة مطلوبة.',
    'current_password' => 'كلمة المرور غير صحيحة.',
    'date' => 'حقل :attribute ليس تاريخًا صالحًا.',
    'date_equals' => 'يجب أن يكون حقل :attribute تاريخًا مساويًا لـ :date.',
    'date_format' => ':attribute لا يتطابق مع التنسيق :format.',
    'decimal' => 'يجب أن يحتوي حقل :attribute على :decimal أرقام عشرية.',
    'declined' => 'يجب رفض حقل :attribute.',
    'declined_if' => 'يجب رفض حقل :attribute عندما يكون :other هو :value.',
    'different' => 'يجب أن يكون حقل :attribute و :other مختلفين.',
    'digits' => 'يجب أن يحتوي حقل :attribute على :digits أرقام.',
    'digits_between' => 'يجب أن يحتوي حقل :attribute على أرقام بين :min و :max.',
    'dimensions' => 'حقل :attribute يحتوي على أبعاد صورة غير صالحة.',
    'distinct' => 'حقل :attribute يحتوي على قيمة مكررة.',
    'doesnt_end_with' => 'يجب ألا ينتهي حقل :attribute بأي من القيم التالية: :values.',
    'doesnt_start_with' => 'يجب ألا يبدأ حقل :attribute بأي من القيم التالية: :values.',
    'email' => 'يجب أن يكون حقل :attribute عنوان بريد إلكتروني صالح.',
    'ends_with' => 'يجب أن ينتهي حقل :attribute بأحد القيم التالية: :values.',
    'enum' => 'القيمة المحددة لحقل :attribute غير صالحة.',
    'exists' => ':attribute غير موجود',
    'extensions' => 'يجب أن يكون حقل :attribute ملفًا من نوع: :values.',
    'file' => 'يجب أن يكون حقل :attribute ملفًا.',
    'filled' => 'يجب أن يحتوي حقل :attribute على قيمة.',
    'gt' => [
        'array' => 'يجب أن يحتوي حقل :attribute على أكثر من :value عنصر.',
        'file' => 'يجب أن يكون حجم حقل :attribute أكبر من :value كيلوبايت.',
        'numeric' => 'يجب أن تكون قيمة حقل :attribute أكبر من :value.',
        'string' => 'يجب أن يكون طول حقل :attribute أكبر من :value حروف.',
    ],
    'gte' => [
        'array' => 'يجب أن يحتوي حقل :attribute على :value عنصر أو أكثر.',
        'file' => 'يجب أن يكون حجم حقل :attribute أكبر من أو يساوي :value كيلوبايت.',
        'numeric' => 'يجب أن تكون قيمة حقل :attribute أكبر من أو تساوي :value.',
        'string' => 'يجب أن يكون طول حقل :attribute أكبر من أو يساوي :value حروف.',
    ],
    'hex_color' => 'يجب أن يكون حقل :attribute كود لون سداسي عشري صالح.',
    'image' => 'يجب أن يكون حقل :attribute صورة.',
    'in' => 'القيمة المحددة لحقل :attribute غير صالحة.',
    'in_array' => 'يجب أن يكون حقل :attribute موجودًا في :other.',
    'integer' => 'يجب أن يكون حقل :attribute عددًا صحيحًا.',
    'ip' => 'يجب أن يكون حقل :attribute عنوان IP صالحًا.',
    'ipv4' => 'يجب أن يكون حقل :attribute عنوان IPv4 صالحًا.',
    'ipv6' => 'يجب أن يكون حقل :attribute عنوان IPv6 صالحًا.',
    'json' => 'يجب أن يكون حقل :attribute نص JSON صالح.',
    'list' => 'يجب أن يكون حقل :attribute قائمة.',
    'lowercase' => 'يجب أن يكون حقل :attribute بحروف صغيرة.',
    'lt' => [
        'array' => 'يجب أن يحتوي حقل :attribute على أقل من :value عنصر.',
        'file' => 'يجب أن يكون حجم حقل :attribute أقل من :value كيلوبايت.',
        'numeric' => 'يجب أن تكون قيمة حقل :attribute أقل من :value.',
        'string' => 'يجب أن يكون طول حقل :attribute أقل من :value حروف.',
    ],
    'lte' => [
        'array' => 'يجب ألا يحتوي حقل :attribute على أكثر من :value عنصر.',
        'file' => 'يجب أن يكون حجم حقل :attribute أقل من أو يساوي :value كيلوبايت.',
        'numeric' => 'يجب أن تكون قيمة حقل :attribute أقل من أو تساوي :value.',
        'string' => 'يجب أن يكون طول حقل :attribute أقل من أو يساوي :value حروف.',
    ],
    'mac_address' => 'يجب أن يكون حقل :attribute عنوان MAC صالح.',
    'max' => [
        'array' => 'يجب ألا يحتوي حقل :attribute على أكثر من :max عنصر.',
        'file' => 'يجب ألا يتجاوز حجم حقل :attribute :max كيلوبايت.',
        'numeric' => 'يجب ألا تكون قيمة حقل :attribute أكبر من :max.',
        'string' => 'يجب ألا يتجاوز طول :attribute :max حروف.',
    ],
    'max_digits' => 'يجب ألا يحتوي حقل :attribute على أكثر من :max أرقام.',
    'mimes' => 'يجب أن يكون حقل :attribute ملفًا من نوع: :values.',
    'mimetypes' => 'يجب أن يكون حقل :attribute ملفًا من نوع: :values.',
    'min' => [
        'array' => 'يجب أن يحتوي حقل :attribute على الأقل :min عنصر.',
        'file' => 'يجب ألا يقل حجم حقل :attribute عن :min كيلوبايت.',
        'numeric' => 'يجب ألا تقل قيمة حقل :attribute عن :min.',
        'string' => 'يجب ألا يقل طول :attribute عن :min حروف.',
    ],
    'min_digits' => 'يجب أن يحتوي حقل :attribute على الأقل :min أرقام.',
    'missing' => 'يجب أن يكون حقل :attribute مفقودًا.',
    'missing_if' => 'يجب أن يكون حقل :attribute مفقودًا عندما يكون :other هو :value.',
    'missing_unless' => 'يجب أن يكون حقل :attribute مفقودًا إلا إذا كان :other هو :value.',
    'missing_with' => 'يجب أن يكون حقل :attribute مفقودًا عندما تكون القيم :values موجودة.',
    'missing_with_all' => 'يجب أن يكون حقل :attribute مفقودًا عندما تكون جميع القيم :values موجودة.',
    'multiple_of' => 'يجب أن تكون قيمة حقل :attribute من مضاعفات :value.',
    'not_in' => 'الاختيار :attribute غير صالح.',
    'not_regex' => 'صيغة :attribute غير صالحة.',
    'numeric' => 'يجب أن يكون حقل :attribute عددًا.',
    'password' => [
        'letters' => 'يجب أن يحتوي حقل :attribute على حرف واحد على الأقل.',
        'mixed' => ':attribute يجب أن تحتوي على حروف كبيرة وحروف صغيرة وأرقام وعلامات حاصة',
        'numbers' => 'يجب أن يحتوي حقل :attribute على رقم واحد على الأقل.',
        'symbols' => 'يجب أن يحتوي حقل :attribute على رمز واحد على الأقل.',
        'uncompromised' => 'حقل :attribute الذي تم إدخاله قد ظهر في تسريب بيانات. يرجى اختيار :attribute مختلف.',
    ],
    'present' => 'يجب أن يكون حقل :attribute موجودًا.',
    'present_if' => 'يجب أن يكون حقل :attribute موجودًا عندما يكون :other هو :value.',
    'present_unless' => 'يجب أن يكون حقل :attribute موجودًا ما لم يكن :other هو :value.',
    'present_with' => 'يجب أن يكون حقل :attribute موجودًا عندما تكون :values موجودة.',
    'present_with_all' => 'يجب أن يكون حقل :attribute موجودًا عندما تكون :values موجودة.',
    'prohibited' => 'يجب أن يكون حقل :attribute محظورًا.',
    'prohibited_if' => 'يجب أن يكون حقل :attribute محظورًا عندما يكون :other هو :value.',
    'prohibited_unless' => 'يجب أن يكون حقل :attribute محظورًا ما لم يكن :other في :values.',
    'prohibits' => 'يمنع حقل :attribute من وجود :other.',
    'regex' => 'صيغة :attribute غير صالحة.',
    'required' => 'يجب إدخال :attribute.',
    'required_array_keys' => 'يجب أن يحتوي حقل :attribute على إدخالات لـ: :values.',
    'required_if' => 'يجب ملء حقل :attribute عندما يكون :other هو :value.',
    'required_if_accepted' => 'يجب ملء حقل :attribute عندما يتم قبول :other.',
    'required_if_declined' => 'يجب ملء حقل :attribute عندما يتم رفض :other.',
    'required_unless' => 'يجب ملء حقل :attribute ما لم يكن :other في :values.',
    'required_with' => 'يجب ملء حقل :attribute عندما تكون :values موجودة.',
    'required_with_all' => 'يجب ملء حقل :attribute عندما تكون :values موجودة.',
    'required_without' => 'يجب ملء حقل :attribute عندما لا تكون :values موجودة.',
    'required_without_all' => 'يجب ملء حقل :attribute عندما لا تكون أي من :values موجودة.',
    'same' => 'يجب أن يتطابق حقل :attribute مع :other.',
    'size' => [
        'array' => 'يجب أن يحتوي حقل :attribute على :size عناصر.',
        'file' => 'يجب أن يكون حجم حقل :attribute :size كيلوبايت.',
        'numeric' => 'يجب أن يكون حقل :attribute :size.',
        'string' => 'يجب أن يحتوي حقل :attribute على :size حرفًا.',
    ],
    'starts_with' => 'يجب أن يبدأ حقل :attribute بأحد القيم التالية: :values.',
    'string' => 'يجب أن يكون حقل :attribute سلسلة.',
    'timezone' => 'يجب أن يكون حقل :attribute منطقة زمنية صالحة.',
    'unique' => ':attribute موجود بالفعل.',
    'uploaded' => 'فشل تحميل حقل :attribute.',
    'uppercase' => 'يجب أن يكون حقل :attribute بحروف كبيرة.',
    'url' => 'يجب أن يكون حقل :attribute عنوان URL صالح.',
    'ulid' => 'يجب أن يكون حقل :attribute ULID صالح.',
    'uuid' => 'يجب أن يكون حقل :attribute UUID صالح.',
    'phone' => 'يجب أن يكون حقل :attribute رقمًا صالحًا.',
    'current_password_required' => 'يجب ادخال كلمة المرور الحالية',
    'and_more' => '(و :count أخطاء أخرى)',

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Here you may specify custom validation messages for attributes using the
    | convention "attribute.rule" to name the lines. This makes it quick to
    | specify a specific custom language line for a given attribute rule.
    |
    */

    'custom' => [
        'email' => [
            'exists' => 'البريد الإلكتروني غير موجود بالنظام',
            'unique' => 'البريد الإلكتروني مسجل بالفعل. يرجي اختيار بريد آخر',
            'max' => 'الحد الأقصى المسموح به هو :max حرفًا',
            'email' => 'يرجى إدخال عنوان بريد إلكتروني صالح. مثال: name@example.com'
        ],
        'phone' => [
            'exists' => 'رقم الهاتف غير موجود بالنظام',
            'unique' => 'رقم الهاتف مسجل بالفعل. يرجي اختيار رقم آخر',
            'min' => 'يجب أن يكون رقم الجوال على الأقل :min أرقام.',
            'max' => 'يجب أن يكون رقم الجوال على الأكثر :max أرقام.',
        ],
        'country_code' => [
            'regex' => 'يجب أن يبدأ رمز البلد بعلامة (+) متبوعة برقم يتراوح من 1 إلى 4 أرقام، على سبيل المثال، (+20).',
        ],
        'address' => [
            'min' => 'يجب أن يكون العنوان على الأقل :min أحرف.',
            'max' => 'يجب ألا يتجاوز العنوان :max أحرف.',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | The following language lines are used to swap our attribute placeholder
    | with something more reader friendly such as "E-Mail Address" instead
    | of "email". This simply helps us make our message more expressive.
    |
    */

    'attributes' => [
        'country_code' => 'كود الدولة',
        'country_id' => 'الدولة',
        'name' => 'الاسم',
        'email' => 'البريد الإلكتروني',
        'phone' => 'الهاتف',
        'password' => 'كلمة المرور',
        "website" => "رابط الموقع",
        "facebook" => "رابط فيسبوك",
        "linkedin" => "رابط لينكدإن",
        "x" => "رابط X",
        "youtube" => "رابط يوتيوب",
        "description" => "الوصف",
        "category_id" => "القسم",
        "role_id" => "الدور",
        "parent_id" => "القسم الرئيسي",
        "type" => "النوع",
        'name' => 'الاسم',
        'permissions' => 'الصلاحيات',
        'name.ar' => 'الاسم بالعربي',
        'name.en' => 'الاسم بالانجليزي',
        'permissions' => 'الصلاحيات',
        "sent_to" => "مرسل إلي",
        "title" => "العنوان",
        "message" => "الرسالة",
        "all" => "الكل",
        'title' => 'العنوان',
        'body' => 'المحتوى',
        'current_password' => 'كلمة المرور الحالية',
        'image' => 'الصورة',
        'title.ar' => 'العنوان بالعربي',
        'title.en' => 'العنوان بالانجليزي',
        'body.ar' => 'المحتوى بالعربي',
        'body.en' => 'المحتوى بالانجليزي',
        'short_description.ar' => 'الوصف القصير بالعربي',
        'short_description.en' => 'الوصف القصير بالانجليزي',
        'description.ar' => 'الوصف بالعربي',
        'description.en' => 'الوصف بالانجليزي',
        'link' => 'الرابط',
        'question.ar' => 'السؤال بالعربي',
        'question.en' => 'السؤال بالانجليزي',
        'answer.ar' => 'الإجابة بالعربي',
        'answer.en' => 'الإجابة بالانجليزي',
        'stock_threshold' => "حد المخزون",
        'max_units_per_order' => "أقصى عدد للوحدات في الطلب",
        'markup_fee_type' => "نوع رسوم التسوية",
        'markup_fee_value' => "قيمة رسوم التسوية",
        'facebook' => "رابط فيسبوك",
        'x' => "رابط X",
        'tiktok' => "رابط تيك توك",
        'youtube' => "رابط يوتيوب",
        'snapchat' => "رابط سناب شات",
        'linkedin' => "رابط لينكدان",
        'base_price_source' => 'مصدر السعر الأساسي',
        'identifier' => 'المعرف',
        'discount_value' => 'قيمة الخصم',
        'delivery_type' => 'نوع التوصيل',
        'discount_type' => 'نوع الخصم',
        'discount_value' => 'قيمة الخصم',
        'value' => 'القيمة',
        'variant_name' => 'الاسم',
        'base_price' => 'السعر الأساسي',
        'variant_values' => 'القيم',
        'quantity' => 'الكمية',
        'code' => 'الكود',
        'expiry_date' => 'تاريخ الانتهاء',
        'custom_markup_fee_type' => 'نوع رسوم هامش الربح المخصصة',
        'custom_markup_fee_value' => 'رسوم هامش الربح المخصصة',
        'amount' => 'المبلغ',
        'whatsapp' => 'واتساب',
        'telegram' => 'تيليجرام',
        'notes' => 'لملاحظات',
        'guest_name' => 'الاسم'
    ]

];
