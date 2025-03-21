<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use App\Models\User;

class UserHasRoleOrPermission implements Rule
{
    protected string $type; // "role" or "permission"
    protected array $values; // Allowed roles/permissions

    public function __construct(string $type, array $values)
    {
        $this->type = $type;
        $this->values = $values;
    }

    public function passes($attribute, $value)
    {
        $user = User::find($value);

        if (!$user) {
            return false; // User does not exist
        }

        if ($this->type === 'role' && $user->hasRole($this->values)) {
            return true; // User has required role
        }

        if ($this->type === 'permission' && $user->hasPermissionTo($this->values)) {
            return true; // User has required permission
        }

        return false;
    }

    public function message()
    {
        return "The selected user must have one of the required {$this->type}s: " . implode(', ', $this->values);
    }
}
