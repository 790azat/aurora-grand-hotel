<?php

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

    'accepted' => ':attribute դաշտը պետք է ընդունված լինի։',
    'accepted_if' => ':attribute դաշտը պետք է ընդունված լինի, երբ :other դաշտի արժեքը :value է։',
    'active_url' => ':attribute դաշտը պետք է լինի վավեր URL։',
    'after' => ':attribute դաշտը պետք է լինի :date-ից հետո ամսաթիվ։',
    'after_or_equal' => ':attribute դաշտը պետք է լինի :date կամ դրանից հետո ամսաթիվ։',
    'alpha' => ':attribute դաշտը պետք է պարունակի միայն տառեր։',
    'alpha_dash' => ':attribute դաշտը պետք է պարունակի միայն տառեր, թվեր, գծիկներ և ընդգծման նշաններ։',
    'alpha_num' => ':attribute դաշտը պետք է պարունակի միայն տառեր և թվեր։',
    'any_of' => ':attribute դաշտն անվավեր է։',
    'array' => ':attribute դաշտը պետք է լինի զանգված։',
    'array_keys' => ':attribute դաշտը պետք է պարունակի միայն հետևյալ բանալիները՝ :values։',
    'ascii' => ':attribute դաշտը պետք է պարունակի միայն մեկբայթանոց տառաթվային նիշեր և սիմվոլներ։',
    'base64' => ':attribute դաշտը պետք է լինի վավեր Base64 տող։',
    'before' => ':attribute դաշտը պետք է լինի :date-ից առաջ ամսաթիվ։',
    'before_or_equal' => ':attribute դաշտը պետք է լինի :date կամ դրանից առաջ ամսաթիվ։',
    'between' => [
        'array' => ':attribute դաշտը պետք է պարունակի :min-ից :max տարր։',
        'file' => ':attribute դաշտը պետք է լինի :min-ից :max կիլոբայթ։',
        'numeric' => ':attribute դաշտը պետք է լինի :min-ի և :max-ի միջև։',
        'string' => ':attribute դաշտը պետք է պարունակի :min-ից :max նիշ։',
    ],
    'boolean' => ':attribute դաշտը պետք է լինի true կամ false։',
    'can' => ':attribute դաշտը պարունակում է չթույլատրված արժեք։',
    'confirmed' => ':attribute դաշտի հաստատումը չի համընկնում։',
    'contains' => ':attribute դաշտում բացակայում է պարտադիր արժեքը։',
    'current_password' => 'Գաղտնաբառը սխալ է։',
    'date' => ':attribute դաշտը պետք է լինի վավեր ամսաթիվ։',
    'date_equals' => ':attribute դաշտը պետք է լինի :date ամսաթիվը։',
    'date_format' => ':attribute դաշտը պետք է համապատասխանի :format ձևաչափին։',
    'decimal' => ':attribute դաշտը պետք է ունենա :decimal տասնորդական նիշ։',
    'declined' => ':attribute դաշտը պետք է մերժված լինի։',
    'declined_if' => ':attribute դաշտը պետք է մերժված լինի, երբ :other դաշտի արժեքը :value է։',
    'different' => ':attribute և :other դաշտերը պետք է տարբեր լինեն։',
    'digits' => ':attribute դաշտը պետք է պարունակի :digits թվանշան։',
    'digits_between' => ':attribute դաշտը պետք է պարունակի :min-ից :max թվանշան։',
    'dimensions' => ':attribute դաշտի պատկերի չափերն անվավեր են։',
    'distinct' => ':attribute դաշտը պարունակում է կրկնվող արժեք։',
    'doesnt_contain' => ':attribute դաշտը չպետք է պարունակի հետևյալներից որևէ մեկը՝ :values։',
    'doesnt_end_with' => ':attribute դաշտը չպետք է ավարտվի հետևյալներից որևէ մեկով՝ :values։',
    'doesnt_start_with' => ':attribute դաշտը չպետք է սկսվի հետևյալներից որևէ մեկով՝ :values։',
    'email' => ':attribute դաշտը պետք է լինի վավեր էլ. հասցե։',
    'encoding' => ':attribute դաշտը պետք է կոդավորված լինի :encoding կոդավորմամբ։',
    'ends_with' => ':attribute դաշտը պետք է ավարտվի հետևյալներից մեկով՝ :values։',
    'enum' => 'Ընտրված :attribute արժեքն անվավեր է։',
    'exists' => 'Ընտրված :attribute արժեքն անվավեր է։',
    'extensions' => ':attribute դաշտը պետք է ունենա հետևյալ ընդլայնումներից մեկը՝ :values։',
    'file' => ':attribute դաշտը պետք է լինի ֆայլ։',
    'filled' => ':attribute դաշտը պետք է արժեք ունենա։',
    'gt' => [
        'array' => ':attribute դաշտը պետք է պարունակի :value-ից ավելի տարր։',
        'file' => ':attribute դաշտը պետք է լինի :value կիլոբայթից մեծ։',
        'numeric' => ':attribute դաշտը պետք է լինի :value-ից մեծ։',
        'string' => ':attribute դաշտը պետք է պարունակի :value-ից ավելի նիշ։',
    ],
    'gte' => [
        'array' => ':attribute դաշտը պետք է պարունակի առնվազն :value տարր։',
        'file' => ':attribute դաշտը պետք է լինի :value կիլոբայթից մեծ կամ հավասար։',
        'numeric' => ':attribute դաշտը պետք է լինի :value-ից մեծ կամ հավասար։',
        'string' => ':attribute դաշտը պետք է պարունակի առնվազն :value նիշ։',
    ],
    'hex_color' => ':attribute դաշտը պետք է լինի վավեր տասնվեցական գույն։',
    'image' => ':attribute դաշտը պետք է լինի պատկեր։',
    'in' => 'Ընտրված :attribute արժեքն անվավեր է։',
    'in_array' => ':attribute դաշտը պետք է առկա լինի :other-ում։',
    'in_array_keys' => ':attribute դաշտը պետք է պարունակի հետևյալ բանալիներից առնվազն մեկը՝ :values։',
    'integer' => ':attribute դաշտը պետք է լինի ամբողջ թիվ։',
    'ip' => ':attribute դաշտը պետք է լինի վավեր IP հասցե։',
    'ipv4' => ':attribute դաշտը պետք է լինի վավեր IPv4 հասցե։',
    'ipv6' => ':attribute դաշտը պետք է լինի վավեր IPv6 հասցե։',
    'json' => ':attribute դաշտը պետք է լինի վավեր JSON տող։',
    'list' => ':attribute դաշտը պետք է լինի ցուցակ։',
    'lowercase' => ':attribute դաշտը պետք է գրված լինի փոքրատառերով։',
    'lt' => [
        'array' => ':attribute դաշտը պետք է պարունակի :value-ից պակաս տարր։',
        'file' => ':attribute դաշտը պետք է լինի :value կիլոբայթից փոքր։',
        'numeric' => ':attribute դաշտը պետք է լինի :value-ից փոքր։',
        'string' => ':attribute դաշտը պետք է պարունակի :value-ից պակաս նիշ։',
    ],
    'lte' => [
        'array' => ':attribute դաշտը չպետք է պարունակի :value-ից ավելի տարր։',
        'file' => ':attribute դաշտը պետք է լինի :value կիլոբայթից փոքր կամ հավասար։',
        'numeric' => ':attribute դաշտը պետք է լինի :value-ից փոքր կամ հավասար։',
        'string' => ':attribute դաշտը պետք է պարունակի առավելագույնը :value նիշ։',
    ],
    'mac_address' => ':attribute դաշտը պետք է լինի վավեր MAC հասցե։',
    'max' => [
        'array' => ':attribute դաշտը չպետք է պարունակի :max-ից ավելի տարր։',
        'file' => ':attribute դաշտը չպետք է գերազանցի :max կիլոբայթը։',
        'numeric' => ':attribute դաշտը չպետք է գերազանցի :max-ը։',
        'string' => ':attribute դաշտը չպետք է պարունակի :max-ից ավելի նիշ։',
    ],
    'max_digits' => ':attribute դաշտը չպետք է պարունակի :max-ից ավելի թվանշան։',
    'mimes' => ':attribute դաշտը պետք է լինի հետևյալ տեսակի ֆայլ՝ :values։',
    'mimetypes' => ':attribute դաշտը պետք է լինի հետևյալ տեսակի ֆայլ՝ :values։',
    'min' => [
        'array' => ':attribute դաշտը պետք է պարունակի առնվազն :min տարր։',
        'file' => ':attribute դաշտը պետք է լինի առնվազն :min կիլոբայթ։',
        'numeric' => ':attribute դաշտը պետք է լինի առնվազն :min։',
        'string' => ':attribute դաշտը պետք է պարունակի առնվազն :min նիշ։',
    ],
    'min_digits' => ':attribute դաշտը պետք է պարունակի առնվազն :min թվանշան։',
    'missing' => ':attribute դաշտը պետք է բացակայի։',
    'missing_if' => ':attribute դաշտը պետք է բացակայի, երբ :other դաշտի արժեքը :value է։',
    'missing_unless' => ':attribute դաշտը պետք է բացակայի, եթե :other դաշտի արժեքը :value չէ։',
    'missing_with' => ':attribute դաշտը պետք է բացակայի, երբ առկա է :values։',
    'missing_with_all' => ':attribute դաշտը պետք է բացակայի, երբ առկա են :values։',
    'multiple_of' => ':attribute դաշտը պետք է լինի :value-ի բազմապատիկ։',
    'not_in' => 'Ընտրված :attribute արժեքն անվավեր է։',
    'not_regex' => ':attribute դաշտի ձևաչափն անվավեր է։',
    'numeric' => ':attribute դաշտը պետք է լինի թիվ։',
    'password' => [
        'letters' => ':attribute դաշտը պետք է պարունակի առնվազն մեկ տառ։',
        'mixed' => ':attribute դաշտը պետք է պարունակի առնվազն մեկ մեծատառ և մեկ փոքրատառ։',
        'numbers' => ':attribute դաշտը պետք է պարունակի առնվազն մեկ թիվ։',
        'symbols' => ':attribute դաշտը պետք է պարունակի առնվազն մեկ սիմվոլ։',
        'uncompromised' => 'Նշված :attribute արժեքը հայտնվել է տվյալների արտահոսքում։ Խնդրում ենք ընտրել այլ :attribute։',
    ],
    'present' => ':attribute դաշտը պետք է առկա լինի։',
    'present_if' => ':attribute դաշտը պետք է առկա լինի, երբ :other դաշտի արժեքը :value է։',
    'present_unless' => ':attribute դաշտը պետք է առկա լինի, եթե :other դաշտի արժեքը :value չէ։',
    'present_with' => ':attribute դաշտը պետք է առկա լինի, երբ առկա է :values։',
    'present_with_all' => ':attribute դաշտը պետք է առկա լինի, երբ առկա են :values։',
    'prohibited' => ':attribute դաշտն արգելված է։',
    'prohibited_if' => ':attribute դաշտն արգելված է, երբ :other դաշտի արժեքը :value է։',
    'prohibited_if_accepted' => ':attribute դաշտն արգելված է, երբ :other դաշտն ընդունված է։',
    'prohibited_if_declined' => ':attribute դաշտն արգելված է, երբ :other դաշտը մերժված է։',
    'prohibited_unless' => ':attribute դաշտն արգելված է, եթե :other դաշտի արժեքը :values-ի մեջ չէ։',
    'prohibits' => ':attribute դաշտն արգելում է :other դաշտի առկայությունը։',
    'regex' => ':attribute դաշտի ձևաչափն անվավեր է։',
    'required' => ':attribute դաշտը պարտադիր է։',
    'required_array_keys' => ':attribute դաշտը պետք է պարունակի գրառումներ հետևյալի համար՝ :values։',
    'required_if' => ':attribute դաշտը պարտադիր է, երբ :other դաշտի արժեքը :value է։',
    'required_if_accepted' => ':attribute դաշտը պարտադիր է, երբ :other դաշտն ընդունված է։',
    'required_if_declined' => ':attribute դաշտը պարտադիր է, երբ :other դաշտը մերժված է։',
    'required_unless' => ':attribute դաշտը պարտադիր է, եթե :other դաշտի արժեքը :values-ի մեջ չէ։',
    'required_with' => ':attribute դաշտը պարտադիր է, երբ առկա է :values։',
    'required_with_all' => ':attribute դաշտը պարտադիր է, երբ առկա են :values։',
    'required_without' => ':attribute դաշտը պարտադիր է, երբ :values առկա չէ։',
    'required_without_all' => ':attribute դաշտը պարտադիր է, երբ :values դաշտերից ոչ մեկն առկա չէ։',
    'same' => ':attribute դաշտը պետք է համընկնի :other դաշտի հետ։',
    'size' => [
        'array' => ':attribute դաշտը պետք է պարունակի :size տարր։',
        'file' => ':attribute դաշտը պետք է լինի :size կիլոբայթ։',
        'numeric' => ':attribute դաշտը պետք է լինի :size։',
        'string' => ':attribute դաշտը պետք է պարունակի :size նիշ։',
    ],
    'starts_with' => ':attribute դաշտը պետք է սկսվի հետևյալներից մեկով՝ :values։',
    'string' => ':attribute դաշտը պետք է լինի տող։',
    'timezone' => ':attribute դաշտը պետք է լինի վավեր ժամային գոտի։',
    'unique' => 'Այս :attribute արժեքն արդեն զբաղված է։',
    'uploaded' => ':attribute դաշտի վերբեռնումը ձախողվեց։',
    'uppercase' => ':attribute դաշտը պետք է գրված լինի մեծատառերով։',
    'url' => ':attribute դաշտը պետք է լինի վավեր URL։',
    'ulid' => ':attribute դաշտը պետք է լինի վավեր ULID։',
    'uuid' => ':attribute դաշտը պետք է լինի վավեր UUID։',

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
            'unique' => 'Այս էլ. հասցեով հաշիվ արդեն գոյություն ունի։',
        ],
        'terms' => [
            'accepted' => 'Շարունակելու համար խնդրում ենք ընդունել ամրագրման պայմանները։',
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
        'first_name' => 'անուն',
        'last_name' => 'ազգանուն',
        'email' => 'էլ. հասցե',
        'current_password' => 'ընթացիկ գաղտնաբառ',
        'new_password' => 'նոր գաղտնաբառ',
        'check_in' => 'ժամանման ամսաթիվ',
        'check_out' => 'մեկնման ամսաթիվ',
        'room_type' => 'համարի տեսակ',
        'promo_input' => 'պրոմոկոդ',
        'reference' => 'ամրագրման համար',
        'card_number' => 'քարտի համար',
        'card_expiry' => 'գործողության ժամկետ',
        'card_cvc' => 'CVC',
        'card_name' => 'քարտապանի անուն',
        'body' => 'կարծիք',
        'delete_password' => 'գաղտնաբառ',
    ],

];
