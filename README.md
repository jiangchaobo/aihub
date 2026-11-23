# AIHub — Laravel AI Platform Starter

这是一个可合并到 Laravel 12 项目的可运行 MVP 代码包，提供：
- 用户认证（使用 Laravel Sanctum，沿用 Laravel 默认 User）
- AI 渠道与模型管理数据结构
- OpenAI 兼容 Chat Completions Provider
- 多轮聊天 API（同步版）
- 会话与消息持久化
- API Key 创建与 SHA-256 校验中间件
- 使用量记录基础结构
- 管理员渠道/模型 CRUD API 的基础实现

> 重要范围说明：这是可运行的第一阶段 starter，不是已经完成生产验收的全功能 SaaS。
> RAG、Qdrant、Agent 工作流、支付、SSE、完整账务冻结/结算、前端管理后台、自动故障切换和生产级审计尚未实现。
> 请先在测试环境部署、补充测试与安全审查，再上线。

## 环境要求
- PHP 8.2+
- Laravel 12（或经依赖兼容性验证的 Laravel 版本）
- MySQL 8+
- Composer
- PHP cURL、OpenSSL、BCMath 扩展
- 一个 OpenAI-compatible API 服务商的 API Key

## 安装

```bash
composer create-project laravel/laravel aihub
cd aihub
php artisan install:api
```

将本压缩包内 `app/`、`config/`、`database/`、`routes/` 目录合并复制到 Laravel 项目根目录（不要覆盖你已有的文件；按需合并）。

配置 `.env`：

```env
AI_DEFAULT_TIMEOUT=60
```

配置 `config/ai.php` 已包含在包中。

运行：

```bash
php artisan migrate
php artisan serve
```

## 添加渠道和模型

使用 Tinker：

```bash
php artisan tinker
```

```php
$channel = App\Models\AiChannel::create([
    'name' => 'My OpenAI Compatible Channel',
    'provider' => 'openai_compatible',
    'base_url' => 'https://api.openai.com/v1',
    'api_key_encrypted' => Illuminate\Support\Facades\Crypt::encryptString('YOUR_API_KEY'),
    'timeout' => 60,
    'priority' => 100,
    'status' => true,
]);

App\Models\AiModel::create([
    'channel_id' => $channel->id,
    'model_code' => 'gpt-4.1-mini',
    'display_name' => 'GPT 4.1 mini',
    'model_type' => 'chat',
    'input_price' => '0.00000000',
    'output_price' => '0.00000000',
    'price_unit' => 'per_1m_tokens',
    'context_limit' => 128000,
    'enabled' => true,
]);
```

模型名称和供应商可用能力以你的 API 服务商实际配置为准。

## API

所有 `/api/v1` 用户接口需要 Sanctum Bearer Token。

| Method | URL | 说明 |
|---|---|---|
| GET | `/api/v1/models` | 列出启用模型 |
| POST | `/api/v1/conversations` | 创建会话 |
| GET | `/api/v1/conversations` | 我的会话列表 |
| GET | `/api/v1/conversations/{id}` | 会话详情 |
| DELETE | `/api/v1/conversations/{id}` | 删除会话 |
| POST | `/api/v1/chat/completions` | 发送消息 |
| POST | `/api/v1/api-keys` | 创建平台 API Key（仅返回一次明文） |

### 获取 Sanctum Token
可在你自己的登录流程中使用 Sanctum token：
```php
$token = $user->createToken('web')->plainTextToken;
```

### Chat 请求示例
```bash
curl -X POST http://127.0.0.1:8000/api/v1/chat/completions \
  -H "Authorization: Bearer YOUR_SANCTUM_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "model_id": 1,
    "conversation_id": null,
    "message": "你好，请介绍一下自己"
  }'
```

## 平台 API Key
创建：
```bash
curl -X POST http://127.0.0.1:8000/api/v1/api-keys \
  -H "Authorization: Bearer YOUR_SANCTUM_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"name":"My integration"}'
```
完整 key 只在创建响应中出现一次。数据库只保存 SHA-256 hash。
中间件 `auth.platform_key` 可用于后续开放 API 路由。

## 安全注意
1. `APP_DEBUG=false` 用于生产环境，妥善保管 `APP_KEY`。
2. 渠道 API Key 使用 Laravel Crypt 加密；备份 `APP_KEY`，否则无法解密历史密钥。
3. 不要记录 Authorization、供应商密钥或完整敏感提示词。
4. 生产环境需补充限流、配额预扣/结算、幂等账务、审计、监控和异常处理。
5. 当前聊天示例为同步请求，网络调用不应放在数据库事务内；代码先保存用户消息、调用模型、再保存助手消息。
6. 供应商错误响应不会原样返回给最终用户。
7. 对外 API 还需加权限、速率限制、额度校验及请求大小限制。

## 目录
- `app/Services/AI`: Provider 抽象与驱动
- `app/Http/Controllers/Api`: 用户 API
- `app/Http/Controllers/Admin`: 管理 API
- `app/Http/Middleware`: 平台 Key 认证
- `database/migrations`: 核心数据表
- `routes/api.php`: API 路由

## 后续建议
按顺序开发 SSE 流式输出、Redis 队列、RAG（Qdrant）、Agent 工具执行引擎、钱包账务和 Vue 前端。
