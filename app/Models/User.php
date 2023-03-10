<?php

namespace App\Models;

use App\Traits\Multitenantable;
use Barryvdh\LaravelIdeHelper\Eloquent;
use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\HigherOrderBuilderProxy;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Notifications\DatabaseNotificationCollection;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Lab404\Impersonate\Models\Impersonate;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\Permission\Traits\HasRoles;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;

/**
 * App\Models\User
 *
 * @property int $id
 * @property string $first_name
 * @property string $last_name
 * @property string $email
 * @property string|null $contact
 * @property string|null $region_code
 * @property int $is_active
 * @property string|null $language
 * @property Carbon|null $email_verified_at
 * @property string $password
 * @property int $theme_mode
 * @property string $tenant_id
 * @property string|null $remember_token
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read string $full_name
 * @property-read mixed $multi_language_date
 * @property-read HigherOrderBuilderProxy|mixed $plan_name
 * @property-read string $profile_image
 * @property-read MediaCollection|Media[] $media
 * @property-read int|null $media_count
 * @property-read DatabaseNotificationCollection|DatabaseNotification[] $notifications
 * @property-read int|null $notifications_count
 * @property-read Collection|\Spatie\Permission\Models\Permission[] $permissions
 * @property-read int|null $permissions_count
 * @property-read Collection|\Spatie\Permission\Models\Role[] $roles
 * @property-read int|null $roles_count
 * @property-read Subscription $subscription
 * @property-read Collection|Subscription[] $subscriptions
 * @property-read int|null $subscriptions_count
 * @property-read MultiTenant $tenant
 *
 * @method static UserFactory factory(...$parameters)
 * @method static Builder|User newModelQuery()
 * @method static Builder|User newQuery()
 * @method static Builder|User permission($permissions)
 * @method static Builder|User query()
 * @method static Builder|User role($roles, $guard = null)
 * @method static Builder|User whereContact($value)
 * @method static Builder|User whereCreatedAt($value)
 * @method static Builder|User whereEmail($value)
 * @method static Builder|User whereEmailVerifiedAt($value)
 * @method static Builder|User whereFirstName($value)
 * @method static Builder|User whereId($value)
 * @method static Builder|User whereIsActive($value)
 * @method static Builder|User whereLanguage($value)
 * @method static Builder|User whereLastName($value)
 * @method static Builder|User wherePassword($value)
 * @method static Builder|User whereRegionCode($value)
 * @method static Builder|User whereRememberToken($value)
 * @method static Builder|User whereTenantId($value)
 * @method static Builder|User whereThemeMode($value)
 * @method static Builder|User whereUpdatedAt($value)
 * @mixin Eloquent
 */
class User extends Authenticatable implements HasMedia, MustVerifyEmail
{
    use HasFactory, Notifiable, InteractsWithMedia, HasRoles, Impersonate, BelongsToTenant, Multitenantable;

    protected $table = 'users';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'contact',
        'region_code',
        'is_active',
        'email_verified_at',
        'password',
        'theme_mode',
        'language',
        'tenant_id',
    ];

    const LANGUAGES = [
        'ar' => 'Arabic',
        'zh' => 'Chinese',
        'en' => 'English',
        'fr' => 'French',
        'de' => 'German',
        'pt' => 'Portuguese',
        'ru' => 'Russian',
        'es' => 'Spanish',
        'tr' => 'Turkish',
    ];

    const ALL_LANGUAGES = [
        'af' => 'Afrikaans',
        'sq' => 'Albanian - shqip',
        'am' => 'Amharic - ????????????',
        'ar' => 'Arabic - ??????????????',
        'an' => 'Aragonese - aragon??s',
        'hy' => 'Armenian - ??????????????',
        'ast' => 'Asturian - asturianu',
        'az' => 'Azerbaijani - az??rbaycan dili',
        'eu' => 'Basque - euskara',
        'be' => 'Belarusian - ????????????????????',
        'bn' => 'Bengali - ???????????????',
        'bs' => 'Bosnian - bosanski',
        'br' => 'Breton - brezhoneg',
        'bg' => 'Bulgarian - ??????????????????',
        'ca' => 'Catalan - catal??',
        'ckb' => 'Central Kurdish - ?????????? (???????????????? ????????????)',
        'zh' => 'Chinese - ??????',
        'zh-HK' => 'Chinese (Hong Kong) - ??????????????????',
        'zh-CN' => 'Chinese (Simplified) - ??????????????????',
        'zh-TW' => 'Chinese (Traditional) - ??????????????????',
        'co' => 'Corsican',
        'hr' => 'Croatian - hrvatski',
        'cs' => 'Czech - ??e??tina',
        'da' => 'Danish - dansk',
        'nl' => 'Dutch - Nederlands',
        'en' => 'English',
        'en-AU' => 'English (Australia)',
        'en-CA' => 'English (Canada)',
        'en-IN' => 'English (India)',
        'en-NZ' => 'English (New Zealand)',
        'en-ZA' => 'English (South Africa)',
        'en-GB' => 'English (United Kingdom)',
        'en-US' => 'English (United States)',
        'eo' => 'Esperanto - esperanto',
        'et' => 'Estonian - eesti',
        'fo' => 'Faroese - f??royskt',
        'fil' => 'Filipino',
        'fi' => 'Finnish - suomi',
        'fr' => 'French - fran??ais',
        'fr-CA' => 'French (Canada) - fran??ais (Canada)',
        'fr-FR' => 'French (France) - fran??ais (France)',
        'fr-CH' => 'French (Switzerland) - fran??ais (Suisse)',
        'gl' => 'Galician - galego',
        'ka' => 'Georgian - ?????????????????????',
        'de' => 'German - Deutsch',
        'de-AT' => 'German (Austria) - Deutsch (??sterreich)',
        'de-DE' => 'German (Germany) - Deutsch (Deutschland)',
        'de-LI' => 'German (Liechtenstein) - Deutsch (Liechtenstein)',
        'de-CH' => 'German (Switzerland) - Deutsch (Schweiz)',
        'el' => 'Greek - ????????????????',
        'gn' => 'Guarani',
        'gu' => 'Gujarati - ?????????????????????',
        'ha' => 'Hausa',
        'haw' => 'Hawaiian - ????lelo Hawai??i',
        'he' => 'Hebrew - ??????????',
        'hi' => 'Hindi - ??????????????????',
        'hu' => 'Hungarian - magyar',
        'is' => 'Icelandic - ??slenska',
        'id' => 'Indonesian - Indonesia',
        'ia' => 'Interlingua',
        'ga' => 'Irish - Gaeilge',
        'it' => 'Italian - italiano',
        'it-IT' => 'Italian (Italy) - italiano (Italia)',
        'it-CH' => 'Italian (Switzerland) - italiano (Svizzera)',
        'ja' => 'Japanese - ?????????',
        'kn' => 'Kannada - ???????????????',
        'kk' => 'Kazakh - ?????????? ????????',
        'km' => 'Khmer - ???????????????',
        'ko' => 'Korean - ?????????',
        'ku' => 'Kurdish - Kurd??',
        'ky' => 'Kyrgyz - ????????????????',
        'lo' => 'Lao - ?????????',
        'la' => 'Latin',
        'lv' => 'Latvian - latvie??u',
        'ln' => 'Lingala - ling??la',
        'lt' => 'Lithuanian - lietuvi??',
        'mk' => 'Macedonian - ????????????????????',
        'ms' => 'Malay - Bahasa Melayu',
        'ml' => 'Malayalam - ??????????????????',
        'mt' => 'Maltese - Malti',
        'mr' => 'Marathi - ???????????????',
        'mn' => 'Mongolian - ????????????',
        'ne' => 'Nepali - ??????????????????',
        'no' => 'Norwegian - norsk',
        'nb' => 'Norwegian Bokm??l - norsk bokm??l',
        'nn' => 'Norwegian Nynorsk - nynorsk',
        'oc' => 'Occitan',
        'or' => 'Oriya - ???????????????',
        'om' => 'Oromo - Oromoo',
        'ps' => 'Pashto - ????????',
        'fa' => 'Persian - ??????????',
        'pl' => 'Polish - polski',
        'pt' => 'Portuguese - portugu??s',
        'pt-BR' => 'Portuguese (Brazil) - portugu??s (Brasil)',
        'pt-PT' => 'Portuguese (Portugal) - portugu??s (Portugal)',
        'pa' => 'Punjabi - ??????????????????',
        'qu' => 'Quechua',
        'ro' => 'Romanian - rom??n??',
        'mo' => 'Romanian (Moldova) - rom??n?? (Moldova)',
        'rm' => 'Romansh - rumantsch',
        'ru' => 'Russian - ??????????????',
        'gd' => 'Scottish Gaelic',
        'sr' => 'Serbian - ????????????',
        'sh' => 'Serbo-Croatian - Srpskohrvatski',
        'sn' => 'Shona - chiShona',
        'sd' => 'Sindhi',
        'si' => 'Sinhala - ???????????????',
        'sk' => 'Slovak - sloven??ina',
        'sl' => 'Slovenian - sloven????ina',
        'so' => 'Somali - Soomaali',
        'st' => 'Southern Sotho',
        'es' => 'Spanish - espa??ol',
        'es-AR' => 'Spanish (Argentina) - espa??ol (Argentina)',
        'es-419' => 'Spanish (Latin America) - espa??ol (Latinoam??rica)',
        'es-MX' => 'Spanish (Mexico) - espa??ol (M??xico)',
        'es-ES' => 'Spanish (Spain) - espa??ol (Espa??a)',
        'es-US' => 'Spanish (United States) - espa??ol (Estados Unidos)',
        'su' => 'Sundanese',
        'sw' => 'Swahili - Kiswahili',
        'sv' => 'Swedish - svenska',
        'tg' => 'Tajik - ????????????',
        'ta' => 'Tamil - ???????????????',
        'tt' => 'Tatar',
        'te' => 'Telugu - ??????????????????',
        'th' => 'Thai - ?????????',
        'ti' => 'Tigrinya - ????????????',
        'to' => 'Tongan - lea fakatonga',
        'tr' => 'Turkish - T??rk??e',
        'tk' => 'Turkmen',
        'tw' => 'Twi',
        'uk' => 'Ukrainian - ????????????????????',
        'ur' => 'Urdu - ????????',
        'ug' => 'Uyghur',
        'uz' => 'Uzbek - o???zbek',
        'vi' => 'Vietnamese - Ti???ng Vi???t',
        'wa' => 'Walloon - wa',
        'cy' => 'Welsh - Cymraeg',
        'fy' => 'Western Frisian',
        'xh' => 'Xhosa',
        'yi' => 'Yiddish',
        'yo' => 'Yoruba - ??d?? Yor??b??',
        'zu' => 'Zulu - isiZulu',
    ];

    const FLAG = [
        'ar' => 'assets/img/LanguageImage/arabic.svg',
        'en' => 'assets/img/LanguageImage/english.png',
        'zh' => 'assets/img/LanguageImage/china.png',
        'fr' => 'assets/img/LanguageImage/france.png',
        'de' => 'assets/img/LanguageImage/german.png',
        'pt' => 'assets/img/LanguageImage/portuguese.png',
        'ru' => 'assets/img/LanguageImage/russian.jpeg',
        'es' => 'assets/img/LanguageImage/spain.png',
        'tr' => 'assets/img/LanguageImage/turkish.png',
    ];

    const PROFILE = 'profile';

    const ALL = 2;

    const STEP_3 = 3;

    const IS_ACTIVE = 1;

    protected $appends = ['full_name', 'profile_image', 'plan_name', 'multi_language_date'];

    /**
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * @var array
     */
    public static $rules = [
        'first_name' => 'required|string|max:180',
        'last_name' => 'required|string|max:180',
        'email' => 'required|email:filter|max:191|unique:users,email,',
    ];

    /**
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function getProfileImageAttribute(): string
    {
        /** @var Media $media */
        $media = $this->getMedia(self::PROFILE)->first();
        if (! empty($media)) {
            return $media->getFullUrl();
        }

        return asset('web/media/avatars/150-2.jpg');
    }

    public function getFullNameAttribute(): string
    {
        return $this->first_name.' '.$this->last_name;
    }

    public function getMultiLanguageDateAttribute()
    {
        setLocalLang(getLogInUser()->language ?? 'en');

        return Carbon::parse($this->created_at)->isoFormat('Do MMMM YYYY  hh:mm A');
    }

    /**
     * @return HigherOrderBuilderProxy|mixed
     */
    public function getPlanNameAttribute()
    {
        $subscription = $this->subscriptions->where('status', Subscription::ACTIVE)->first();

        if (! empty($subscription)) {
            return $subscription->plan->name;
        }

        return '';
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class, 'tenant_id', 'tenant_id')
            ->where('status', Subscription::ACTIVE);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class, 'tenant_id', 'tenant_id');
    }

    public function markEmailAsVerified()
    {
        return DB::table('users')->where('id', $this->id)
            ->update(['email_verified_at' => \Carbon\Carbon::now()]);
    }
}
