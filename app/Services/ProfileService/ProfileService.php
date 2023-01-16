<?php

namespace App\Services\ProfileService;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Support\Collection;

class ProfileService
{
    public function __construct(protected Customer $customer, protected User $user)
    {
    }

    /**
     * @param string $text
     * @param int $limit
     * @return Collection
     */
    public function searchByText(string $text, int $limit = 15): Collection
    {
        $query = $this->customer->query();
        if (strlen($text) <= 0) {
            return $query->orderBy('id', 'DESC')->limit($limit)->get();
        }
        $cTable = $this->customer->getTable();
        $uTable = $this->user->getTable();

        return $query
            ->join($uTable, $uTable . '.id', '=', $cTable . '.user_id')
            ->where(function ($builder) use ($uTable, $cTable, $text) {
                $builder->where($cTable . '.name', 'like', '%' . $text . '%');
                $builder->orWhere($cTable . 'family', 'like', '%' . $text . '%');
                $builder->orWhere($uTable . 'email', 'like', '%' . $text . '%');
            })->orderBy('id', 'DESC')
            ->limit($limit)->get([$cTable . '.*']);
    }

}
