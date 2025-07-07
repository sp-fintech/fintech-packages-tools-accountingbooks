<?php

namespace Apps\Fintech\Packages\Accounting\Books;

use Apps\Fintech\Packages\Accounting\Books\Model\AppsFintechAccountingBooks;
use Apps\Fintech\Packages\Accounting\Tools\Accountshierarchies\AccountingToolsAccountshierarchies;
use Apps\Fintech\Packages\Accounts\Users\AccountsUsers;
use System\Base\BasePackage;

class AccountingBooks extends BasePackage
{
    protected $modelToUse = AppsFintechAccountingBooks::class;

    protected $packageName = 'accountingbooks';

    public $accountingbooks;

    public function getAccountingBookById($id)
    {
        $this->ffStore = $this->ff->store($this->ffStoreToUse);

        $this->setFFRelations(true);

        $this->getFirst('id', $id);

        if ($this->model) {
            $book = $this->model->toArray();

            $book['accounts'] = [];
            if ($this->model->getaccounts()) {
                $book['accounts'] = $this->model->getaccounts()->toArray();
            }
        } else {
            if ($this->ffData) {
                $book = $this->jsonData($this->ffData, true);
            }
        }

        if ($book) {
            if ($book['accounts'] && count($book['accounts']) > 0) {
                $bookAccounts = [];

                foreach ($book['accounts'] as $account) {
                    $bookAccounts[$account['uuid']] = $account;
                    if ($account['balance'] < 0) {
                        $warnings[$account['uuid']] = $account['name'] . ' has negative balance!';
                    }
                }

                $book['accounts'] = $bookAccounts;
            }

            return $book;
        }

        return false;
    }

    public function addAccountingBook($data)
    {
        if (!$data = $this->checkData($data)) {
            return false;
        }

        if (!isset($data['account_id'])) {
            $data['account_id'] = $this->access->auth->account()['id'];
        }

        $data['status'] = 'open';

        if ($this->add($data)) {
            $this->addResponse('Book Added successfully');

            return true;
        }

        $this->addResponse('Error adding book', 1);

        return false;
    }

    public function updateAccountingBook($data)
    {
        $accountingbooks = $this->getById($id);

        if ($accountingbooks) {
            //
            $this->addResponse('Success');

            return;
        }

        $this->addResponse('Error', 1);
    }

    protected function checkData($data)
    {
        if (!isset($data['user_id'])) {
            $this->addResponse('Please provide user', 1);

            return false;
        }

        $accountsUsersPackage = $this->usePackage(AccountsUsers::class);

        if (!$accountsUsersPackage->getById((int) $data['user_id'])) {
            $this->addResponse('Please provide correct user ID', 1);

            return false;
        }

        if (isset($data['id'])) {
            return $data;
        }

        if (!isset($data['accounts_hierarchy_id'])) {
            $this->addResponse('Please provide hierarchy', 1);

            return false;
        }

        $accountsHierarchiesPackage = $this->usePackage(AccountingToolsAccountshierarchies::class);

        if (!$accountsHierarchy = $accountsHierarchiesPackage->getById((int) $data['accounts_hierarchy_id'])) {
            $this->addResponse('Please provide correct accounts hierarchy ID', 1);

            return false;
        }

        $data['accounts_hierarchy'] = $accountsHierarchy['hierarchy'];

        return $data;
    }

    public function removeAccountingBook($id)
    {
        $accountingbook = $this->getById($id);

        if (!$accountingbook) {
            $this->addResponse('Book with ID not found', 1);

            return false;
        }

        if ($this->remove($accountingbook['id'])) {
            $this->addResponse('Book removed');

            return true;
        }

        $this->addResponse('Error removing book', 1);
    }

    public function getBooksByAccountId($data)
    {
        if ($this->config->databasetype === 'db') {
            $conditions =
                [
                    'conditions'    => 'account_id = :account_id:',
                    'bind'          =>
                        [
                            'account_id'       => (int) $data['account_id'],
                        ]
                ];
        } else {
            $conditions =
                [
                    'conditions'    => ['account_id', '=', (int) $data['account_id']]
                ];
        }

        $booksArr = $this->getByParams($conditions);

        $books = [];

        if ($booksArr && count($booksArr) > 0) {
            foreach ($booksArr as $book) {
                $books[$book['id']] = $this->getAccountingBookById($book['id']);
            }

            return $books;
        }

        return [];
    }
}