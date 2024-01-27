<?php

namespace Skillcraft\ContactManager\Models;

use Botble\Base\Casts\SafeContent;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Skillcraft\ContactManager\Enums\ContactDataTypeEnum;
use Skillcraft\Core\Models\CoreModel as BaseModel;

/**
 * @method static \Botble\Base\Models\BaseQueryBuilder<static> query()
 */
class ContactEmail extends BaseModel
{
    use SoftDeletes;

    protected $table = 'contact_emails';

    protected $fillable = [
        'contact_id',
        'email',
        'type',
    ];

    protected $casts = [
        'type' => ContactDataTypeEnum::class,
        'email' => SafeContent::class,
    ];

    public function modelInstallSchema(): void
    {
        Schema::create($this->getTable(), function (Blueprint $table) {
            $table->id();
            $table->foreignId('contact_id')->index()
                ->onDelete('cascade');
            $table->string('email', 120)->nullable();
            $table->string('type', 60)->default(ContactDataTypeEnum::HOME);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(ContactManager::class);
    }
}
