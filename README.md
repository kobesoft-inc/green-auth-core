# Green Auth Core

[![Latest Version on Packagist](https://img.shields.io/packagist/v/kobesoft/green-auth-core.svg?style=flat-square)](https://packagist.org/packages/kobesoft/green-auth-core)
[![Total Downloads](https://img.shields.io/packagist/dt/kobesoft/green-auth-core.svg?style=flat-square)](https://packagist.org/packages/kobesoft/green-auth-core)

LaravelとFilament管理パネルの深い統合を提供する包括的な認証・認可パッケージです。マルチガード対応、階層グループ、ロール、および洗練された権限システムを備えたエンタープライズグレードのユーザー管理システムを提供します。

## 機能

- 🔐 **完全な認証システム** - ユーザー登録、ログイン、パスワード管理
- 👥 **マルチガード対応** - 独立した設定を持つ複数の認証ガード
- 🏢 **階層グループ** - 継承機能付きのネストしたグループ構造
- 🎭 **ロールベースアクセス制御** - 柔軟なロールと権限システム
- 🔑 **高度な権限管理** - ドット記法による階層権限
- 📊 **Filament統合** - すぐに使える管理パネルリソース
- 🔒 **パスワードポリシー** - 設定可能な複雑さ要件と有効期限
- 📝 **監査ログ** - ログイン履歴とアクティビティ追跡
- 🌐 **多言語対応** - 英語と日本語の翻訳を内蔵
- ⚡ **簡単インストール** - 対話式セットアップコマンド

## インストール

### Composerでのインストール

Composerを使用してパッケージをインストール：

```bash
composer require kobesoft/green-auth-core
```

### パッケージのインストール

対話式インストールコマンドを実行：

```bash
php artisan green-auth:install
```

このコマンドは以下を実行します：
- 設定のセットアップをガイド
- ベースクラスを継承したカスタムモデルを生成
- Filament管理リソースを作成
- マイグレーションと設定ファイルを公開
- 多言語サポートをセットアップ

#### インストールオプション

```bash
# 特定のインストール手順をスキップ
php artisan green-auth:install --skip-models --skip-migrations

# 既存ファイルを強制上書き
php artisan green-auth:install --force
```

### 手動インストール

手動インストールを希望する場合：

```bash
# 設定ファイルのみ公開
php artisan vendor:publish --tag=green-auth-config

# 全リソースを公開
php artisan vendor:publish --provider="Green\AuthCore\GreenAuthServiceProvider"

# マイグレーション実行
php artisan migrate
```

## ユーザーインターフェース概要

Green Auth CoreはFilament管理パネルの完全統合を提供：

### 認証ページ
- **ログインページ** - メール/ユーザー名対応のカスタマイズ可能ログイン  
- **パスワード変更** - セルフサービスパスワード更新
- **パスワード期限切れ** - 自動パスワード有効期限処理

### 管理リソース
- **ユーザー管理** - 高度なフィルタリング付き完全なユーザーCRUD
- **グループ管理** - ドラッグ&ドロップ対応階層グループ構造
- **ロール管理** - 権限割り当て付きロール作成
- **ログイン履歴** - アクティビティ監視と監査ログ

### 管理アクション
- 一括ユーザー操作（停止、停止解除、パスワードリセット）
- グループ階層管理
- ロールと権限の割り当て
- パスワードポリシーの強制

## コアモデルと機能

### ユーザーモデル

ベースユーザーモデルが提供する機能：

```php
// カスタムユーザーモデルの例
class User extends \Green\AuthCore\Models\BaseUser
{
    // カスタム属性とメソッド
}
```

**主要機能：**
- 設定可能なポリシーによるパスワード有効期限
- アカウント停止/有効化
- Filament統合によるアバター管理
- マルチグループとマルチロール割り当て
- ログインアクティビティ追跡
- パネルアクセス制御

### グループモデル

ネスト構造を持つ階層グループシステム：

```php
class Group extends \Green\AuthCore\Models\BaseGroup
{
    // ネストセット機能を継承
}
```

**機能：**
- 無制限ネストでの親子関係
- ユーザーとロールの割り当て
- 階層を通じた権限継承
- 一括操作サポート

### ロールモデル

柔軟なロールベースアクセス制御：

```php
class Role extends \Green\AuthCore\Models\BaseRole
{
    // ロール固有の機能
}
```

**機能：**
- ユーザーあたり複数ロール
- グループ-ロール割り当て
- 権限集約
- 動的ロールチェック

## 権限管理システム

### 権限アーキテクチャ

Green Auth Coreは洗練された権限システムを実装：

- **階層構造** - ドット記法による権限（例：`users.create`、`posts.edit`）
- **マルチガード対応** - 認証ガードごとに異なる権限セット
- **継承** - ユーザーはグループとロールから権限を継承
- **スーパー管理者** - フルアクセス用の内蔵スーパー管理者権限（`*`）

### カスタム権限の作成

```php
use Green\AuthCore\Permission\BasePermission;

class PostPermissions extends BasePermission
{
    public static function permissions(): array
    {
        return [
            'posts.view' => '投稿の表示',
            'posts.create' => '投稿の作成',
            'posts.edit' => '投稿の編集',
            'posts.delete' => '投稿の削除',
        ];
    }
}
```

### 権限の登録

```php
// サービスプロバイダーで
use Green\AuthCore\Facades\PermissionManager;

PermissionManager::register([
    PostPermissions::class,
    CommentPermissions::class,
]);
```

### 権限チェック

```php
// ユーザー権限チェック
if ($user->hasPermission('posts.create')) {
    // ユーザーは投稿を作成できる
}

// ゲートでのチェック
if (Gate::allows('posts.edit', $post)) {
    // ユーザーはこの投稿を編集できる
}

// Filamentリソースで
public static function canCreate(): bool
{
    return auth()->user()->hasPermission('posts.create');
}
```

## 設定

### マルチガード設定

`config/green-auth.php`で異なる認証コンテキストを設定：

```php
return [
    'guards' => [
        'web' => [
            'models' => [
                'user' => App\Models\User::class,
                'group' => App\Models\Group::class,
                'role' => App\Models\Role::class,
            ],
            'auth' => [
                'login_with_email' => true,
                'login_with_username' => false,
            ],
            'password' => [
                'complexity' => [
                    'min_length' => 8,
                    'require_uppercase' => true,
                    'require_lowercase' => true,
                    'require_numbers' => true,
                    'require_symbols' => false,
                ],
                'expiration' => [
                    'enabled' => true,
                    'days' => 90,
                ],
            ],
        ],
        'admin' => [
            // 管理者ガード用の異なる設定
        ],
    ],
];
```

### パスワードポリシー

```php
'password' => [
    'complexity' => [
        'min_length' => 12,
        'require_uppercase' => true,
        'require_lowercase' => true,
        'require_numbers' => true,
        'require_symbols' => true,
        'min_uppercase' => 2,
        'min_lowercase' => 2,
        'min_numbers' => 2,
        'min_symbols' => 1,
    ],
    'expiration' => [
        'enabled' => true,
        'days' => 60,
        'notify_before_days' => 7,
    ],
],
```

## 高度な使用方法

### ベースモデルの拡張

```php
use Green\AuthCore\Models\BaseUser;
use Green\AuthCore\Models\Concerns\HasCustomAttribute;

class User extends BaseUser
{
    use HasCustomAttribute;
    
    protected $fillable = [
        'name', 'email', 'custom_field',
    ];
    
    public function customRelation()
    {
        return $this->hasMany(CustomModel::class);
    }
}
```

### カスタムFilamentリソース

```php
use Green\AuthCore\Filament\Resources\BaseUserResource;

class UserResource extends BaseUserResource
{
    protected static ?string $model = User::class;
    
    public static function form(Form $form): Form
    {
        return parent::form($form)
            ->schema([
                // カスタムフォームフィールドを追加
                TextInput::make('custom_field'),
            ]);
    }
}
```

### 多言語サポート

パッケージには英語と日本語の翻訳が含まれています。カスタム翻訳を追加：

```php
// resources/lang/ja/green-auth.php
return [
    'custom' => [
        'title' => 'カスタムタイトル',
    ],
];
```

## テスト

テストスイートを実行：

```bash
vendor/bin/pest
```

カバレッジ付きで実行：

```bash
vendor/bin/pest --coverage
```

## セキュリティ

Green Auth CoreはLaravelのセキュリティベストプラクティスに従います：

- bcrypt/argon2によるパスワードハッシュ化
- 全フォームでのCSRF保護
- Eloquent ORMによるSQLインジェクション防止
- FilamentコンポーネントでのXSS保護
- セキュアなパスワード生成
- ログイン試行スロットリング

## 貢献

貢献を歓迎します！プルリクエストを送信する前に、すべてのテストが通ることを確認してください。

## ライセンス

このパッケージは[MITライセンス](LICENSE.md)の下で公開されているオープンソースソフトウェアです。

## サポート

問題や機能リクエストについては、[GitHubイシュートラッカー](https://github.com/kobesoft/green-auth-core/issues)をご利用ください。