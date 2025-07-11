<?php

namespace Green\Auth\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Request as RequestFacade;
use Illuminate\Support\Str;

abstract class BaseLoginLog extends Model
{
    use Concerns\HasModelConfig;

    const UPDATED_AT = null;

    /**
     * 一括代入可能な属性
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'ip_address',
        'user_agent',
        'browser_name',
        'browser_version',
        'platform',
        'device_type',
    ];

    /**
     * キャストすべき属性
     *
     * @var array<string, string>
     */
    protected $casts = [
        'created_at' => 'datetime',
    ];

    /**
     * ユーザーとのリレーション
     *
     * @return BelongsTo ユーザーモデルとの所属関係
     */
    public function user(): BelongsTo
    {
        $userClass = static::getUserClass();
        return $this->belongsTo($userClass, static::getForeignKeyName($userClass));
    }

    /**
     * ログインログを作成
     *
     * @param Model $user ユーザーモデルまたはID
     * @param Request|null $request リクエストインスタンス
     * @return static 作成されたログインログインスタンス
     */
    public static function createLog(Model $user, ?Request $request = null): static
    {
        $request = $request ?? RequestFacade::instance();
        $userAgent = static::parseUserAgent($request->userAgent() ?? '');

        $log = (new static())->fill([
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'browser_name' => $userAgent['browser_name'],
            'browser_version' => $userAgent['browser_version'],
            'platform' => $userAgent['platform'],
            'device_type' => $userAgent['device_type'],
        ]);
        $log->user()->associate($user);
        $log->save();

        return $log;
    }

    /**
     * ユーザーエージェントを解析
     *
     * @param string $userAgent ユーザーエージェント文字列
     * @return array ブラウザ情報配列
     */
    protected static function parseUserAgent(string $userAgent): array
    {
        $result = [
            'browser_name' => 'Unknown',
            'browser_version' => null,
            'platform' => 'Unknown',
            'device_type' => 'Unknown',
        ];

        if (empty($userAgent)) {
            return $result;
        }

        // ブラウザ検出
        $browsers = [
            'Edge' => '/Edge\/([0-9.]+)/',
            'Chrome' => '/Chrome\/([0-9.]+)/',
            'Firefox' => '/Firefox\/([0-9.]+)/',
            'Safari' => '/Version\/([0-9.]+).*Safari/',
            'Opera' => '/Opera\/([0-9.]+)/',
            'Internet Explorer' => '/MSIE ([0-9.]+)/',
        ];

        foreach ($browsers as $browser => $pattern) {
            if (preg_match($pattern, $userAgent, $matches)) {
                $result['browser_name'] = $browser;
                $result['browser_version'] = $matches[1] ?? null;
                break;
            }
        }

        // プラットフォーム検出
        $platforms = [
            'Windows' => '/Windows NT ([0-9.]+)/',
            'macOS' => '/Mac OS X ([0-9_.]+)/',
            'Linux' => '/Linux/',
            'Android' => '/Android ([0-9.]+)/',
            'iOS' => '/OS ([0-9_]+)/',
        ];

        foreach ($platforms as $platform => $pattern) {
            if (preg_match($pattern, $userAgent, $matches)) {
                $result['platform'] = $platform;
                break;
            }
        }

        // デバイスタイプ検出
        if (preg_match('/Mobile|Android|iPhone|iPod/', $userAgent)) {
            $result['device_type'] = 'Mobile';
        } elseif (preg_match('/iPad|Tablet/', $userAgent)) {
            $result['device_type'] = 'Tablet';
        } else {
            $result['device_type'] = 'Desktop';
        }

        return $result;
    }

    /**
     * ブラウザ情報の文字列表現を取得
     *
     * @return string ブラウザ情報文字列
     */
    public function getBrowserInfo(): string
    {
        $parts = [];

        if ($this->browser_name && $this->browser_name !== 'Unknown') {
            $parts[] = $this->browser_name;
            if ($this->browser_version) {
                $parts[] = $this->browser_version;
            }
        }

        if ($this->platform && $this->platform !== 'Unknown') {
            $parts[] = "on {$this->platform}";
        }

        if ($this->device_type && $this->device_type !== 'Unknown') {
            $parts[] = "({$this->device_type})";
        }

        return implode(' ', $parts) ?: 'Unknown Browser';
    }

    /**
     * 特定ユーザーのログインに絞り込むスコープ
     *
     * @param \Illuminate\Database\Eloquent\Builder $query クエリビルダー
     * @param mixed $userId ユーザーID
     * @return \Illuminate\Database\Eloquent\Builder 絞り込まれたクエリ
     */
    public function scopeForUser($query, $userId): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('user_id', $userId);
    }

    /**
     * 日付範囲内のログインに絞り込むスコープ
     *
     * @param \Illuminate\Database\Eloquent\Builder $query クエリビルダー
     * @param mixed $startDate 開始日
     * @param mixed $endDate 終了日
     * @return \Illuminate\Database\Eloquent\Builder 絞り込まれたクエリ
     */
    public function scopeBetweenDates($query, $startDate, $endDate): \Illuminate\Database\Eloquent\Builder
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    /**
     * 特定IPアドレスからのログインに絞り込むスコープ
     *
     * @param \Illuminate\Database\Eloquent\Builder $query クエリビルダー
     * @param string $ipAddress IPアドレス
     * @return \Illuminate\Database\Eloquent\Builder 絞り込まれたクエリ
     */
    public function scopeFromIp($query, string $ipAddress): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('ip_address', $ipAddress);
    }

    /**
     * 最近のログインに絞り込むスコープ
     *
     * @param \Illuminate\Database\Eloquent\Builder $query クエリビルダー
     * @param int $hours 時間数（デフォルト24時間）
     * @return \Illuminate\Database\Eloquent\Builder 絞り込まれたクエリ
     */
    public function scopeRecent($query, int $hours = 24): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('created_at', '>=', now()->subHours($hours));
    }
}
