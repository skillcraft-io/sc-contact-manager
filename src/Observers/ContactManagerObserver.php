<?php

namespace Skillcraft\ContactManager\Observers;

use Illuminate\Support\Str;
use Skillcraft\ContactManager\Models\ContactManager;

class ContactManagerObserver
{
    public function deleted(ContactManager $contact): void
    {
        $contact->phones()->delete();
            $contact->emails()->delete();
            $contact->addresses()->delete();
    }
}
