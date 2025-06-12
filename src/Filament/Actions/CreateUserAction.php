<?php

namespace Green\AuthCore\Filament\Actions;

use Filament\Actions\CreateAction;
use Green\AuthCore\Filament\Actions\Concerns\ManagesUserPasswords;

class CreateUserAction extends CreateAction
{
    use ManagesUserPasswords;

    protected function setUp(): void
    {
        parent::setUp();

        $this->modalHeading(__('green-auth::users.actions.create_user'))
            ->slideOver()
            ->modalWidth('lg')
            ->createAnother(false)
            ->using(function (array $data) {
                return $this->createUser($data);
            });
    }

    /**
     * ユーザー作成処理
     */
    protected function createUser(array $data): \Illuminate\Database\Eloquent\Model
    {
        // モデルクラスを取得
        $modelClass = $this->getModel();
        
        // データ準備
        [$preparedData, $plainPassword, $passwordData] = $this->prepareUserData($data, $modelClass);
        
        // レコード作成
        $record = $modelClass::create($preparedData);
        
        // 通知処理
        $this->notifyUser($record, $plainPassword, $passwordData);
        
        return $record;
    }

}