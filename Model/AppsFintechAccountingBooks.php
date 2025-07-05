<?php

namespace Apps\Fintech\Packages\Accounting\Books\Model;

use Apps\Fintech\Packages\Accounting\Accounts\Model\AppsFintechAccountingAccounts;
use System\Base\BaseModel;

class AppsFintechAccountingBooks extends BaseModel
{
    protected $modelRelations = [];

    public $id;

    public $account_id;

    public $user_id;

    public $name;

    public $fy_start_date;

    public $fy_end_date;

    public $status;

    public $description;

    public $accounts_hierarchy_id;

    public function initialize()
    {
        $this->modelRelations['accounts']['relationObj'] = $this->hasMany(
            'id',
            AppsFintechAccountingAccounts::class,
            'book_id',
            [
                'alias'         => 'accounts'
            ]
        );

        parent::initialize();
    }

    public function getModelRelations()
    {
        if (count($this->modelRelations) === 0) {
            $this->initialize();
        }

        return $this->modelRelations;
    }
}