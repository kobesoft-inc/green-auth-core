<?php

namespace Green\Auth\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class UserPasswordNotification extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * 新しいメッセージインスタンスを作成
     *
     * @param Model $user ユーザーモデル
     * @param string $password パスワード
     * @param mixed $subject 件名
     * @param string $message_ メッセージ
     */
    public function __construct(
        public Model  $user,
        public string $password,
        public        $subject,
        public string $message_
    )
    {
        //
    }

    /**
     * メッセージエンベロープを取得
     *
     * @return Envelope メッセージエンベロープ
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->subject,
        );
    }

    /**
     * メッセージコンテンツ定義を取得
     *
     * @return Content メッセージコンテンツ
     */
    public function content(): Content
    {
        // ガード名を取得
        $guard = $this->user::class::getGuardName();

        // ログインURLを取得
        $loginUrl = $this->getLoginUrl($guard);

        return new Content(
            markdown: 'green-auth::emails.password-notification',
            with: [
                'user' => $this->user,
                'password' => $this->password,
                'message_' => $this->message_,
                'loginUrl' => $loginUrl,
                'username' => $this->user->username ?? $this->user->email,
            ]
        );
    }

    /**
     * ユーザーからガード名を取得
     *
     * @param mixed $userClass ユーザークラス
     * @return string ガード名
     */
    private function getGuardFromUserClass(string $userClass): string
    {
        return $userClass::getGuardName();
    }

    /**
     * ガードからログインURLを取得
     *
     * @param string $guard ガード名
     * @return string|null ログインURL
     */
    private function getLoginUrl(string $guard): ?string
    {
        // Filamentパネルから対応するガードのログインURLを取得
        foreach (filament()->getPanels() as $panel) {
            if ($panel->getAuthGuard() === $guard) {
                return $panel->getLoginUrl() ?? '';
            }
        }

        // ガードが見つからない場合はエラーをスロー
        return null;
    }

    /**
     * メッセージの添付ファイルを取得
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment> 添付ファイル配列
     */
    public function attachments(): array
    {
        return [];
    }
}
