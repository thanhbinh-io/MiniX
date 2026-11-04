# Báo cáo đối chiếu ba dự án — Twitter/X Clone

Ngày khảo sát: **24/10/2026**, múi giờ Asia/Bangkok. Phạm vi: đọc mã nguồn tại chỗ; chỉ tạo báo cáo này.

## Cách đọc và giới hạn kết luận

| Ký hiệu đường dẫn | Thư mục gốc tuyệt đối |
|---|---|
| `OLD/` | `C:/xampp/htdocs/twitter-php-mysql/` |
| `NEW/` | `C:/xampp/htdocs/twitter-clone/` |
| `BE/` | `D:/cloud-project/cloud-storage-be/` |
| `FE/` | `D:/cloud-project/cloud-storage-fe/` |

Mọi đường dẫn rút gọn bên dưới được ghép với gốc tương ứng trong bảng trên. Tên hàm, route hoặc dòng là vị trí bằng chứng, không phải kết quả chạy giả lập. Số dòng áp dụng cho phiên bản đọc trong phiên khảo sát này.

**✅ Hoàn thiện**: luồng có đủ phần chính trong mã nguồn, không thấy lệch trọng yếu với bản cũ trong phạm vi hàng đó. **⚠️ Làm dở/lệch logic**: đã có triển khai nhưng chưa giữ trọn luồng. **❌ Thiếu**: không tìm thấy triển khai tương ứng qua routes, controllers, models, migrations và UI đã kiểm tra. **🧪 Dữ liệu giả**: số liệu nghiệp vụ được gõ cứng thay vì truy vấn dữ liệu. ✅ không có nghĩa đã nghiệm thu chạy thực tế.

Không mở các trang ứng dụng để tránh phát sinh session, view count, đánh dấu đã đọc và ghi log; chưa đo giao diện bằng ảnh chụp, chưa đo hiệu năng, chưa kiểm tra DB đang chạy hay HTTP thực tế. Mức giống UI là so sánh cấu trúc/chức năng từ template, không phải độ giống pixel. Bỏ qua `vendor/`, `node_modules/`, `.git/`, `storage/logs/`, `public/build/` khi liệt kê; không đọc `.env` thật. Có đọc riêng một đoạn validator Laravel trong vendor để tránh kết luận sai về upload. Không chạy migrate, seed, build, Pint sửa mã hay thao tác DB.

**Kết quả chính:** theo 40 đơn vị chức năng/luồng của bản cũ trong Phần 1, có **11 ✅ / 40 = 27,5%**, **21 ⚠️**, **7 ❌**, **1 🧪**. Đây là tỷ lệ giữ nguyên luồng theo tiêu chuẩn chặt, không phải “chỉ viết được 27,5% mã nguồn”. Có 33/40 luồng đã có ít nhất khung triển khai, kể cả một luồng mock. Kiến trúc ước lượng **76/130 = 5,85/10**; không phải điểm chính thức của giảng viên.

## Bước 0 — Khảo sát nền

### 0.1 Công nghệ và tổ chức

| Dự án | Công nghệ đã xác minh | Cách tổ chức và bằng chứng |
|---|---|---|
| cloud-project | Laravel yêu cầu `^12.0`, lock **v12.33.0**; PHP yêu cầu `^8.2`; Sanctum, L5 Swagger; React `^19.1.1`, TypeScript `~5.9.3`, Vite `^7.2.2`, Tailwind 4, React Query, TanStack Router, Redux Toolkit, Zod | `BE/composer.json`, `BE/composer.lock`, `FE/package.json`. Backend API riêng; `BE/routes/api.php`; Controller → FormRequest → Service → Repository; `BE/app/Http/Controllers/Api/File/FileController.php::store`, `BE/app/Services/FileService.php::upload`, `BE/app/Repositories/FileRepository.php`. FE có `src/api`, `src/app`, `src/components`, `src/state` |
| Bản cũ | PHP thuần 8.1+ theo README; PDO MySQL, MySQL 8+/MariaDB 10.5+ theo schema; HTML/CSS/JS thuần, Apache/XAMPP | `OLD/README.md`, `OLD/database/schema.sql:1`, `OLD/app/db.php::db`. `index.php` định tuyến trang; `action.php` xử lý POST; hàm dùng chung trong `app/`; không thấy Composer/npm manifest |
| Bản mới | Laravel yêu cầu `^9.19`, lock **v9.48.0**; PHP manifest `^8.0.2`, PHP CLI kiểm tra được **8.2.12**; Inertia backend lock v0.6.9; Vue 3, Inertia Vue adapter 1, Vite 4, Tailwind 3, Breeze, Ziggy, Sanctum | `NEW/composer.json`, `NEW/composer.lock`, `NEW/package.json`. Laravel render Inertia props trực tiếp sang Vue; Blade chỉ làm shell: `resources/views/app.blade.php`, `resources/js/app.js`. `vite.config.js` và `resources/js/ssr.js` có cấu hình SSR; chưa xác minh SSR server đang bật |

Không đánh đồng phiên bản constraint với phiên bản đã cài; số React/Vue/Vite nêu trên là constraint manifest. PHP CLI 8.2.12 không xác nhận PHP module Apache dùng cùng phiên bản.

### 0.2 Cây thư mục ba cấp

Cây dưới đây nhóm các tệp cấu hình cùng cấp để ngắn gọn; các nhánh nghiệp vụ được giữ. Danh sách cây ba cấp đầy đủ được bổ sung ở Phụ lục A từ filesystem.

```text
D:/cloud-project/
├── .vscode/settings.json
├── cloud-storage-be/
│   ├── app/{Exceptions,Http,Models,Notifications,OpenApi,Providers,Repositories,Services,Support}/
│   ├── bootstrap/{cache,app.php,providers.php}
│   ├── config/{auth,database,filesystems,l5-swagger,sanctum,...}.php
│   ├── database/{factories,migrations,seeders}/
│   ├── docs/{admin,api,user}/
│   ├── resources/{css,js,views}/
│   ├── routes/{api,web,console}.php
│   ├── tests/{Feature,Unit,TestCase.php}
│   ├── storage/{api-docs,app,framework}/
│   └── composer.json, package.json, .env.example, README.md, PROJECT_DOCS.md, AUTH_API.md
└── cloud-storage-fe/
    ├── src/{api,app,components,hooks,state,utils}/
    ├── src/{main.tsx,index.css}
    ├── requirements/*.md
    ├── public/vite.svg
    └── package.json, .env.example, README.md, eslint.config.js, tsconfig*.json, vite.config.ts, vercel.json

C:/xampp/htdocs/twitter-php-mysql/
├── app/{auth,bootstrap,db,helpers,models,view}.php
├── assets/css/style.css
├── assets/js/app.js
├── database/{schema.sql,.htaccess}
├── tests/{check_environment,test_helpers}.php
├── uploads/{avatars,covers,posts}/.gitkeep
├── uploads/.htaccess
└── index.php, action.php, config.php, config.example.php, .htaccess, README.md, PROJECT_REVIEW.md

C:/xampp/htdocs/twitter-clone/
├── app/Http/{Controllers,Middleware,Requests,Kernel.php}
├── app/Models/{User,Tweet,Like,Retweet,Bookmark,TweetView,Report}.php
├── app/Notifications/AppActivityNotification.php
├── app/Policies/TweetPolicy.php
├── app/Support/ReservedUsernames.php
├── app/{Console,Exceptions,Providers}/
├── bootstrap/{cache,ssr,app.php}
├── config/*.php
├── database/factories/{UserFactory,TweetFactory}.php
├── database/migrations/*.php
├── database/seeders/{DatabaseSeeder,TweetsSeeder,AdminSeeder}.php
├── resources/js/{Pages,Layouts,Components,app.js,bootstrap.js,ssr.js}
├── resources/views/{app,welcome}.blade.php
├── resources/css/app.css
├── routes/{web,auth,api,console,channels}.php
├── tests/Feature/{Auth,ExampleTest,InteractionTest,NotificationTest,ProfileAndFollowTest,ProfileTest,SearchAndExploreTest,TimelineAndStorageTest,TweetTest}
├── tests/Unit/ExampleTest.php
├── public/pics/{avatars,banners,Code.png,PlayGuitar.png}
├── public/videos/{Seo.mp4,Sportsman.mp4}
├── public/storage/
└── composer.json, package.json, .env.example, phpunit.xml, README.md, vite.config.js, tailwind.config.js
```

### 0.3 CSDL và media bản cũ

`OLD/database/schema.sql` khai báo **11 bảng**: `users` (dòng 6), `posts` (24), `follows` (41), `likes` (51), `reposts` (61), `bookmarks` (71), `hashtags` (80), `post_hashtags` (85), `notifications` (94), `messages` (108), `reports` (121). Các tương tác dùng khóa chính kép chống trùng; bài trả lời cascade theo cha; quote set null; báo cáo giữ lại khi đối tượng bị xóa bằng set null.

`OLD/uploads/avatars`, `covers`, `posts` tại thời điểm liệt kê chỉ có `.gitkeep`; không có ảnh demo trong các thư mục này. Upload thật được xử lý tại `OLD/app/helpers.php::upload_image`; không suy ra “upload không hoạt động” từ việc thư mục trống. `OLD/assets/js/app.js` có preview ảnh, chặn ảnh >4 MB, bộ đếm ký tự Unicode, confirm thao tác và cuộn chat xuống cuối. `OLD/assets/css/style.css` có responsive và dark theme cố định.

### 0.4 Checklist tiêu chuẩn rút ra từ cloud-project

| Tiêu chuẩn thực sự có | Bằng chứng | Dùng làm thước đo thế nào |
|---|---|---|
| Phân lớp nghiệp vụ | 11 Service, 4 Repository; `BE/app/Services/FileService.php`, `BE/app/Repositories/FileRepository.php` | Tách xử lý phức tạp, transaction và truy vấn tái sử dụng; không bắt buộc bọc mọi Eloquent call bằng Repository |
| Request theo nhóm tính năng | 33 tệp PHP trong `BE/app/Http/Requests`, gồm BaseFormRequest | Tập trung validation, authorization, chuẩn lỗi |
| Chuẩn response/exception | `BE/app/Http/Controllers/Api/BaseApiController.php`, `BE/app/Support/Traits/ApiResponse.php`, `BE/bootstrap/app.php` | Giới hạn dữ liệu public, phản hồi nhất quán |
| Auth và admin gate | `BE/routes/api.php`, `BE/app/Providers/AppServiceProvider.php::boot` | Gate admin có triển khai thật, dù route còn comment TODO cũ |
| Schema có index/quan hệ, dữ liệu demo | `BE/database/migrations/2025_10_15_100841_create_files_table.php`, `BE/database/seeders/DatabaseSeeder.php` | Index theo truy vấn; seed đủ tình huống và quan hệ |
| Tài liệu API phong phú | `BE/PROJECT_DOCS.md`, `AUTH_API.md`, `docs/api/share-integration-guide.md`, `app/OpenApi/OpenApi.php`, `storage/api-docs/api-docs.json` | Mô tả schema, luồng, setup, hợp đồng đầu ra |
| 72 tệp HTTP mẫu | `BE/app/Http/Controllers/Api/**/**.http` | Hỗ trợ demo/test tay; không tính là 72 automated tests |
| Frontend phân lớp, có types/state | `FE/src/state/store.ts`, `FE/src/api/`, `FE/src/app/`, `FE/src/components/`, `FE/tsconfig.app.json` | Tách component/layout/page, chia sẻ state và xử lý lỗi |
| Công cụ chuẩn hóa | `BE/composer.json` có Pint; `FE/package.json` có `lint`; `FE/eslint.config.js` | Có cấu hình và lệnh tái lập, cần thêm bằng chứng chạy |
| Cấu hình host frontend | `FE/vercel.json` rewrite SPA | Có cấu hình triển khai; chưa chứng minh đã deploy |

**Không có cơ sở gán cloud-project điểm tuyệt đối:** chỉ thấy hai PHPUnit test mẫu (`BE/tests/Feature/ExampleTest.php`, `BE/tests/Unit/ExampleTest.php`); các trang `FE/src/api/features/page/*test.page.tsx` là UI thử API, không có script test runner trong `FE/package.json`. Không tìm thấy workflow CI, Dockerfile/compose của dự án, Larastan/PHPStan, LICENSE độc lập hay CONTRIBUTING trong cây nguồn đã lọc. Sail trong composer chỉ là dependency. README backend có badge CI của Laravel upstream, không phải CI riêng. `.env.example` backend chứa APP_KEY không rỗng, `DEV_REPORT=true`, `APP_DEBUG=true`; đây là điểm không nên sao chép. Không ghi lại giá trị khóa trong báo cáo. Tình trạng khóa có dùng thật hay không: **chưa xác định được**.

## Phần 1 — Đối chiếu tính năng và luồng nghiệp vụ

### 1.1 Quy ước ánh xạ backend

Các route tên dưới đây được khai báo trong `NEW/routes/web.php`, trừ auth tại `NEW/routes/auth.php`. Controller mặc định nằm trong `NEW/app/Http/Controllers/`; model trong `NEW/app/Models/`. Các mã schema dùng trong bảng:

| Mã | Migration cụ thể tại `NEW/database/migrations/` |
|---|---|
| U | `2014_10_12_000000_create_users_table.php`; `2026_10_19_130234_add_profile_fields_to_users_table.php`; `2026_10_24_000001_add_role_status_to_users_table.php` |
| T | `2023_01_25_134943_create_tweets_table.php`; `2026_10_19_130254_update_tweets_table_for_user_relationship.php`; `2026_10_19_132938_add_thread_and_quote_to_tweets_table.php` |
| I | `2026_10_19_132950_create_interactions_tables.php` — likes, retweets, bookmarks, tweet_views |
| F | `2026_10_19_134417_create_follows_and_blocks_tables.php` — follows, blocks, mutes |
| N | `2026_10_19_141826_create_notifications_table.php` |
| R | `2026_10_24_000002_create_reports_table.php` |

### 1.2 Ma trận 40 chức năng/luồng

| # | Tính năng/luồng bản cũ | Bản cũ: file/hàm | Bản Laravel: route → controller; model/schema | Trạng thái | Chi tiết thiếu/lệch |
|---|---|---|---|---|---|
| 1 | Đăng ký, chống trùng, tự đăng nhập | `OLD/action.php:14` | `register` → `Auth/RegisteredUserController::store`; User/U | ⚠️ | Có lưu/hash/auth thật. Cũ username 3–20, bắt đầu bằng chữ; mới 3–15 cho phép số/underscore đầu. Cũ name 2–80/email ≤190; mới max255, không min2. Mới thêm password confirmation/reserved names |
| 2 | Đăng nhập username hoặc email, chống thử sai | `OLD/action.php:31` | `login` → `Auth/AuthenticatedSessionController::store`, `Requests/Auth/LoginRequest::authenticate`; User/U | ⚠️ | Mới email-only; không kiểm tra status active khi Auth::attempt. Cũ khóa 10 phút sau 5 lần theo session; mới limiter theo email+IP, dùng decay mặc định |
| 3 | Đăng xuất, hủy session | `OLD/action.php` nhánh logout | `logout` → `Auth/AuthenticatedSessionController::destroy`; User/U | ✅ | POST, logout guard web, invalidate session và regenerate token; UI gọi thật tại TwitterLayout::logout |
| 4 | Đổi mật khẩu hiện tại + xác nhận | `OLD/action.php:185`, `OLD/index.php` settings | `password.update` → `Auth/PasswordController::update`; User/U | ⚠️ | Backend có current_password, confirmed, hash. Form nằm trong Profile/Edit dùng layout gọi route dashboard không tồn tại; không có liên kết Settings trong sidebar chính. Mới không thấy regenerate session trực tiếp như cũ |
| 5 | Đăng bài text/ảnh, 280 ký tự, không để trống | `OLD/action.php:60`, `OLD/app/view.php::compose_form` | `tweets.store` → `TweetController::store`, `StoreTweetRequest`; Tweet/T | ✅ | Trường tweet required_without:file, max280; gắn tác giả từ session, UI Welcome và modal submit thật. Upload và parent/quote xét ở các hàng riêng |
| 6 | Upload ảnh bài viết và preview | `OLD/app/helpers.php::upload_image`, `OLD/assets/js/app.js:14` | `tweets.store` → `TweetController::store`; Tweet.file/T; `StoreTweetRequest:28` | ⚠️ | Cũ 4 MB; mới ảnh 5 MB/video 50 MB. Kiểm tra 5 MB dựa extension gốc, còn mimes dựa nội dung; có thể lệch nhánh phân loại/giới hạn. Không cleanup file nếu tạo DB thất bại. Preview modal chỉ nhận mp4 là video |
| 7 | Sửa nội dung, thay/xóa ảnh của bài mình | `OLD/action.php:95`, `OLD/index.php:202` | Không có route update tweet/controller update/UI sửa; TweetPolicy::update tồn tại nhưng chưa nối | ❌ | Cần giữ validate không rỗng, sync hashtag, rollback ảnh mới và xóa ảnh cũ sau thành công |
| 8 | Chủ bài xóa bài, xóa tương tác/nhánh reply | `OLD/action.php:123`, schema FK posts | `tweets.destroy` → `TweetController::destroy`, TweetPolicy::delete; Tweet/T/I | ✅ | Owner policy + cascade reply/interaction và hook xóa media bài trực tiếp. Media reply bị DB cascade không được hook xử lý: hạn chế tài nguyên nêu ở Phần 3; bản cũ cũng chỉ remove ảnh bài trực tiếp |
| 9 | Feed “Dành cho bạn”: bài gốc + hoạt động repost | `OLD/app/models.php::timeline:34` | `tweets.index` → `TweetController::index:18`; Tweet/T/I | ⚠️ | Mới chỉ top-level tweets order id DESC; không UNION hoạt động retweet, mất nhãn ai repost/thời điểm repost |
| 10 | Feed Following: mình + người theo dõi, gồm repost | `OLD/app/models.php::timeline:38` | `tweets.index?tab=following` → `TweetController::index:35`; User/F, Tweet/T/I | ⚠️ | Có bài của mình/người follow và guest empty; thiếu bài do người follow repost của người thứ ba |
| 11 | Chi tiết, trả lời, điều hướng cha, phân trang reply | `OLD/index.php:176`, `OLD/action.php:60` | `tweets.show`, `tweets.store` → `TweetController::show/store`; Tweet.parent/replies/T | ⚠️ | Mới có reply thật nhưng lấy toàn bộ ASC id; cũ DESC thời gian/id, 15/trang; chưa thấy link cha trên Show.vue. Tham số username URL không được đối chiếu tác giả |
| 12 | Quote và hiển thị bài trích dẫn | `OLD/index.php:193`, `OLD/action.php:60`, `OLD/app/models.php::post_columns` | `tweets.store` → `TweetController::store`; Tweet.quoteOf/T; Components/Tweet.vue::submitQuote | ⚠️ | Lưu quote thật. Không cấm đồng thời parent_id + quote_of_id. UI tăng retweetsCount khi quote nhưng backend chỉ đếm bảng retweets, lệch ngay sau submit |
| 13 | Like/unlike, chống trùng, số đếm | `OLD/action.php:134`, schema likes | `tweets.like` → `LikeController::toggle`; Like/I, Tweet::withInteractionDetails | ✅ | Unique(user_id,tweet_id) có thật, count thật; UI optimistic + rollback. Không tự gán lỗi “thiếu unique” |
| 14 | Repost/undo, chống trùng, số đếm | `OLD/action.php:134`, schema reposts | `tweets.retweet` → `RetweetController::toggle`; Retweet/I | ✅ | Toggle/count/notification có thật. Phân phối hoạt động vào feed và quote-count được chấm riêng ở 9–12 |
| 15 | Bookmark/unbookmark riêng tư | `OLD/action.php:134`, schema bookmarks | `tweets.bookmark` → `BookmarkController::toggle`; Bookmark/I | ✅ | Query theo user đăng nhập; unique kép; không gửi notification khi bookmark |
| 16 | Danh sách bookmark và phân trang | `OLD/index.php:222` | `bookmarks.index` → `BookmarkController::index`; Tweet/Bookmark/T/I | ⚠️ | Có dữ liệu của chính user nhưng get() toàn bộ, không có pagination như cũ |
| 17 | Hồ sơ: tên/avatar/cover/bio/location/website/ngày tham gia/count | `OLD/index.php:109`, `OLD/app/models.php::get_profile` | `users.profile` → `UserProfileController::show`; User/U/F/T; Profile/Show.vue | ✅ | Props/model và withCount thật. Quy tắc sửa hồ sơ, tab repost và rò dữ liệu public được tách riêng |
| 18 | Tab bài gốc/reply/media/likes/reposts | `OLD/index.php:114,146` | `users.profile?tab=...` → `UserProfileController::show`; User.likedTweets, Tweet/T/I | ⚠️ | Có 4 tab; thiếu reposts. Tất cả get() không phân trang; likes order likes.id thay vì thời gian bài như cũ |
| 19 | Sửa hồ sơ + avatar/cover + dọn ảnh cũ | `OLD/action.php:162`, `OLD/index.php` settings | `profile.custom_update` → `UserProfileController::update`; User/U; EditProfileModal.vue | ⚠️ | Có submit thật. Name/bio/website khác giới hạn; website không validate URL/scheme; avatar/banner chỉ jpg/png thay vì thêm webp/gif. Không xóa ảnh cũ và thiếu rollback khi lưu DB lỗi |
| 20 | Follow/unfollow tài khoản active, cấm tự follow | `OLD/action.php:149`, `OLD/app/view.php::follow_form` | `users.follow` → `FollowController::toggle`; User.following/F | ⚠️ | Chặn tự follow nhưng không chặn target suspended; cũng chưa chặn quan hệ block mới bổ sung |
| 21 | Danh sách followers/following, sắp mới nhất | `OLD/index.php:161` | `users.followers/following` → `FollowController::followers/following`; User/F | ⚠️ | Có UI và dữ liệu nhưng get() không limit/order pivot created_at như cũ (cũ limit200 DESC); N+1 exists theo từng user |
| 22 | Tìm tên/username/nội dung, gồm reply | `OLD/index.php:69` | `search` → `SearchController::index`; User/Tweet/U/T; Search.vue | ⚠️ | Mới mặc định Top theo likes, cũ bài mới nhất; mới bỏ replies và không lọc người active; UI chỉ render data, không có trang sau dù backend paginate15 |
| 23 | Hashtag: parse, link, tìm theo tag đã chuẩn hóa | `OLD/app/helpers.php::sync_tags/render_text`, `OLD/index.php:80` | `search?q=#...` → `SearchController::index`; Tweet/T | ⚠️ | Chỉ LIKE nội dung. Không bảng hashtags/pivot, không parse Unicode/dedup; Tweet.vue:400 hiển thị plain text nên không click hashtag trong bài |
| 24 | Mention: link @username và notification dedup | `OLD/app/helpers.php::render_text/notify_mentions:148` | Không parser/link/dispatch mention; TweetController::store, AppActivityNotification | ❌ | Thiếu tra active user, unique mention không phân biệt hoa thường và loại trừ người đã nhận reply/quote |
| 25 | Xu hướng thật: top6 hashtag trong 7 ngày | `OLD/app/models.php::get_trends:57` | `explore` → `SearchController::explore:83`; TwitterLayout.vue:331 | 🧪 | Mảng topic/count 42.8K, 28.1K... và sidebar viết cứng; bài trong Explore là query thật, không phải cả trang giả |
| 26 | Gợi ý follow: 4 user active, mới nhất, không phải mình/đã follow | `OLD/app/models.php::get_suggestions:65`, `OLD/app/view.php::page_footer` | Không thấy endpoint/props/component gợi ý trong layout mới | ❌ | Hai biến randImg trong layout không phải gợi ý user đã triển khai |
| 27 | Thông báo like/repost/reply/follow, bỏ tự thông báo | `OLD/action.php:82,144,157`, `OLD/app/helpers.php::notify` | Like/Retweet/Follow/TweetController → AppActivityNotification; User.notifications/N | ✅ | Dispatch database thật; like/repost/reply kiểm tra khác tác giả, follow chặn tự follow. Không phải realtime push |
| 28 | Thông báo quote cho tác giả gốc | `OLD/action.php:83` | TweetController::store chỉ dispatch reply; AppActivityNotification không có caller quote | ❌ | Quote được lưu nhưng không gửi thông báo |
| 29 | Inbox notification, badge, mở đúng đối tượng | `OLD/index.php:229`, `OLD/app/models.php::unread_notifications` | `notifications.index` → `NotificationController::index`; N; Notifications.vue | ⚠️ | Có list/badge thật; GET tự mark tất cả read, cũ chỉ nút POST. UI không link bài dù payload có tweet_id; không có trang sau 20 kết quả |
| 30 | Đánh dấu tất cả thông báo đã đọc theo chủ sở hữu | `OLD/action.php:207` | `notifications.read_all` → `NotificationController::markAllAsRead`; User.notifications/N | ✅ | Scope theo user hiện tại; không thao tác notification user khác. Hiệu năng mark collection nêu riêng |
| 31 | Gửi tin nhắn 1–2000 ký tự, người nhận active, khác mình | `OLD/action.php:196` | Không route/controller/model/migration messages; TwitterLayout.vue:157 | ❌ | Nút Messages không có href và không có handler |
| 32 | Danh sách hội thoại, preview, lịch sử 100 tin, đánh dấu seen | `OLD/index.php:250` | Không Page/Controller/Message schema | ❌ | Cũ lấy contacts tối đa100, sắp tên; tin mới nhất100 rồi hiển thị ASC; mở hội thoại ghi seen_at cho tin gửi đến mình |
| 33 | Badge tin nhắn chưa xem + notification message | `OLD/app/models.php::unread_messages:93`, `OLD/action.php:203`, `OLD/app/view.php::nav_link` | Không triển khai; shared props chỉ unread_notifications_count | ❌ | Cần badge từ messages và đường dẫn notification đến hội thoại |
| 34 | Báo cáo bài viết, lý do 5–500 ký tự | `OLD/action.php:212`, `OLD/index.php:317` | `reports.store` → `ReportController::store`; Report/R; Tweet.vue::submitReport | ⚠️ | Form/API thật, thêm chống trùng/tự báo cáo; lý do chuyển enum + description optional nên không giữ yêu cầu diễn giải ≥5 ký tự |
| 35 | Báo cáo tài khoản qua form | `OLD/index.php:317` nhận user, `OLD/action.php:214` | `reports.store type=user` → `ReportController::store`; Report/R | ⚠️ | Backend có, nhưng Profile/Show.vue chỉ menu mute/block, không có UI báo cáo tài khoản. Cũ có form nhận user qua URL; không khẳng định cũ có nút profile riêng |
| 36 | Admin xem tổng user/post/report pending | `OLD/index.php:333` | `admin.dashboard` → `Admin/AdminDashboardController::index`; User/Tweet/Report/U/T/R | ✅ | Query count thật, thêm suspended/new today; Dashboard.vue dùng props |
| 37 | Admin danh sách user, role/status, chặn user thường | `OLD/index.php:365`, `OLD/app/auth.php::must_admin` | `admin.users` → AdminDashboardController::users; AdminMiddleware; User/U/F/R | ✅ | Có auth+admin, paginate20, tìm kiếm/count; cũ limit200. Không tính thêm toggle role vào yêu cầu cũ |
| 38 | Đình chỉ/mở khóa, chặn tài khoản bị đình chỉ | `OLD/action.php:228`, `OLD/app/auth.php::user:11` | `admin.users.toggle_status` → AdminDashboardController::toggleUserStatus; CheckSuspended, LoginRequest/U | ⚠️ | Admin không tự đình chỉ/admin khác, đúng. Nhưng login mới vẫn nhận suspended; middleware chỉ áp một nhóm route, chỉ chặn non-GET; password/auth/admin group ngoài nhóm này |
| 39 | Xử lý/bỏ qua báo cáo, ghi chú, xem lại lịch sử | `OLD/action.php:238`, `OLD/index.php:344` | `admin.reports`, `admin.reports.resolve` → AdminDashboardController::reports/resolveReport; Report/R | ⚠️ | Có resolved/dismissed/note thật, thêm resolver/time. Note max1000 thay vì500; pagination thiếu withQueryString làm mất filter ở trang sau; lịch sử còn chịu lỗi cascade ở hàng40 |
| 40 | Admin xóa bài vi phạm nhưng giữ báo cáo | `OLD/action.php:123`, `OLD/database/schema.sql:132` | `admin.reports.delete_tweet`, `admin.tweets.delete` → AdminDashboardController::deleteTweetFromReport/deleteTweet; Tweet/Report/T/R | ⚠️ | FK report.tweet_id cascade, cũ set null. Xóa tweet xóa luôn report rồi update model report đã mất; không giữ lịch sử xử lý theo ý định controller |

### 1.3 Quy tắc ngầm cần khôi phục hoặc quyết định công khai

| Quy tắc cũ | Sai khác mới và hậu quả | Bằng chứng |
|---|---|---|
| Feed là hoạt động, không chỉ bài viết | Người A follow B phải thấy bài C do B repost; lần repost có thời điểm riêng. Dedupe frontend chỉ theo tweet.id cũng sẽ gộp các hoạt động nếu chỉ sửa backend | `OLD/app/models.php::timeline` UNION ALL; `NEW/TweetController::index` trong đường dẫn controller đã quy định; `NEW/resources/js/Pages/Welcome.vue:38` |
| Feed/search/bookmark/profile sort created_at DESC rồi id DESC; phân trang15 | Mới sort id, get() nhiều nơi, reply ASC; dữ liệu import có thời gian khác id sẽ đảo thứ tự | `OLD/app/models.php::find_posts:26`, `NEW/app/Http/Controllers/{TweetController,UserProfileController,BookmarkController,SearchController}.php` |
| Không đồng thời reply và quote | Có thể POST cả hai ID; nhánh reply return sớm, luồng quote không đầy đủ | `OLD/action.php:65`, `NEW/app/Http/Requests/StoreTweetRequest:29`, `NEW/app/Http/Controllers/TweetController:118` |
| Repost_count chỉ đếm repost; quote là bài độc lập | UI tăng retweetsCount khi quote nhưng withCount chỉ retweets | `OLD/app/models.php:8`, `NEW/resources/js/Components/Tweet.vue:243`, `NEW/app/Models/Tweet.php:80` |
| Hashtag lowercase, Unicode, tối đa80 ký tự/tag, unique trong bài | LIKE chuỗi không thay thế được thống kê tag, và chưa tái đồng bộ khi sửa | `OLD/app/helpers.php::sync_tags:136`; schema hashtags/pivot; `NEW/app/Http/Controllers/SearchController` |
| Mention loại trùng, bỏ self, bỏ người đã nhận reply/quote, chỉ active | Mới thiếu cả parser và dispatch; tránh gửi đôi khi bổ sung | `OLD/app/helpers.php::notify_mentions`, `OLD/action.php:84` |
| Notification cũ giữ unread cho tới POST; click mở bài/hội thoại | Mới mở trang tự mark cả trang chưa xem; chỉ link người gửi | `OLD/index.php:238`, `NEW/app/Http/Controllers/NotificationController:25`, `NEW/resources/js/Pages/Notifications.vue` |
| Suspended mất tư cách đăng nhập; không follow tài khoản suspended | Mới vẫn đăng nhập và gọi một số route ngoài middleware | `OLD/app/auth.php:11`, `OLD/action.php:42,152`; `NEW/routes/auth.php`, `NEW/routes/web.php`, LoginRequest/FollowController |
| Website chỉ HTTP(S), ≤255; name2–80, bio160 | Mới website string≤100, name≤255 không min, bio280; cần phân biệt cải tiến chủ động với lệch ngoài ý muốn | `OLD/action.php:167`, `NEW/app/Http/Controllers/UserProfileController:85` |
| Upload ảnh 4MB, JPEG/PNG/WEBP/GIF; tên ngẫu nhiên, cleanup lỗi | Mới phân tán 5/10/50MB, avatar mất WEBP/GIF, dùng tên theo timestamp và không cleanup ảnh cũ | `OLD/app/helpers.php::upload_image`, `NEW/app/Http/Controllers/UserProfileController::update`, `StoreTweetRequest` |
| Báo cáo sống sau khi bài/user đích bị xóa | Cascade làm mất audit trail; update report sau delete không khôi phục được row | `OLD/database/schema.sql:132`, `NEW/database/migrations/2026_10_24_000002_create_reports_table.php`, AdminDashboardController::deleteTweetFromReport |
| Post chỉ sửa bởi chủ; chủ hoặc admin được xóa | Mới owner policy đúng cho delete; admin đi route riêng; chưa có update route dù policy có | `OLD/action.php:98,127`, `NEW/app/Policies/TweetPolicy.php::update/delete`, `NEW/routes/web.php` |

CSRF, escape nội dung và session không được tính thành các tính năng riêng để tránh tăng mẫu số tùy ý: cũ `action.php:9`, helpers::e/check_csrf, bootstrap session; mới `app/Http/Kernel.php` web middleware + Vue interpolation + AuthenticatedSessionController. Cả hai đều có nền tảng này. Cũ SQL động tên bảng trong toggle là whitelist, giá trị dùng prepared statement (`OLD/app/db.php::sql`), không nên gán SQL injection chỉ vì có SQL chuỗi.

### 1.4 Chức năng mới ngoài bản cũ

| Chức năng bổ sung | Bằng chứng mới | Mức xác nhận |
|---|---|---|
| Quên/reset mật khẩu bằng token/email, confirm password | `NEW/routes/auth.php`, `Auth/PasswordResetLinkController.php`, `Auth/NewPasswordController.php`, các Page Auth, migration password_resets | Có backend/form; email giao thực tế chưa xác minh. Bản cũ xác nhận ngoài phạm vi tại `OLD/README.md` |
| Xác minh email | `NEW/routes/auth.php`, `Auth/VerifyEmailController.php`, Page VerifyEmail | ⚠️ Có scaffolding nhưng User không implements MustVerifyEmail (`User.php:5,11`), route nghiệp vụ không có verified; không coi hoàn chỉnh |
| Remember me, sửa email, xóa tài khoản | `NEW/Requests/Auth/LoginRequest.php`, `NEW/ProfileController.php`, Profile/Partials | Có backend; trang settings bị lỗi route dashboard như hàng4 |
| Upload video | `StoreTweetRequest`, `TweetController::store`, Welcome.vue | Có backend/preview một số định dạng; chưa kiểm tra playback thực tế |
| View count dedup 2 giờ | `TweetController::show:71`, TweetView, migration I | Có lưu dữ liệu; check-then-insert chưa chống race, chưa giới hạn tăng trưởng bảng |
| Block/mute | `BlockMuteController`, User relations, migration F, Profile/Show.vue | ⚠️ Có toggle và lọc home/profile; chưa áp đồng nhất search/explore/detail/like/reply/follow |
| Copy link, optimistic interaction, toast | `Components/Tweet.vue::toggleLike/toggleRetweet/toggleBookmark/copyShareLink` | Có thao tác; copy báo thành công trước khi await clipboard; cần xử lý lỗi |
| Tìm Top theo likes và Latest/People riêng | `SearchController::index`, Search.vue | Query thật; pagination UI thiếu |
| Admin đổi role; thêm thống kê ngày, resolver/time | `AdminDashboardController::toggleUserRole/index/resolveReport`, Report/R | Có code và UI; chưa có test riêng admin/report trong bộ test đã tìm |

### 1.5 Thống kê và cách dùng

| Nhóm | Số hàng | Tỷ lệ |
|---|---:|---:|
| ✅ Hoàn thiện theo phạm vi hàng | 11 | 27,5% |
| ⚠️ Có triển khai nhưng lệch/thiếu luồng | 21 | 52,5% |
| ❌ Chưa có | 7 | 17,5% |
| 🧪 Dùng dữ liệu giả | 1 | 2,5% |
| Tổng | 40 | 100% |

Các hàng ✅: **3,5,8,13,14,15,17,27,30,36,37**. Không dùng mức giống giao diện hay số test để tự nâng tỷ lệ này. Hàng24 gộp mention link và notification; hàng31–33 tách gửi, đọc, unread tin nhắn vì là ba luồng độc lập. Tính năng mới không bù trừ tính năng cũ bị thiếu.

## Phần 2 — Giao diện và trải nghiệm

### 2.1 So sánh màn hình

| Màn hình | Bản cũ | Bản mới | Mức giống cấu trúc/chức năng | Dữ liệu thật và thiếu/khác |
|---|---|---|---|---|
| Login/register | `OLD/index.php:44` | `NEW/resources/js/Pages/Auth/{Login,Register}.vue` | Trung bình | Form useForm submit thật; mất username login; UI tiếng Anh, quy tắc register khác |
| Home/newsfeed | `OLD/index.php:28`, models::timeline | `NEW/resources/js/Pages/Welcome.vue`, TwitterLayout.vue | Khá về bố cục | Props tweets thật; hai tab thật; mất nhãn/entry repost; Show more thay pager |
| Composer | `OLD/app/view.php::compose_form` | Welcome.vue, TwitterLayout.vue modal | Khá | Đăng thật, preview/count/loading; bản mới lặp code composer, icon GIF/emoji giả tương tác |
| Chi tiết + reply | `OLD/index.php:176` | `NEW/resources/js/Pages/Tweet/Show.vue`, Components/Tweet.vue | Trung bình | Reply thật; đổi thứ tự, không paging/link cha; detail lặp logic card; thiếu quote action tương đương popover của card |
| Sửa bài | `OLD/index.php:202` | Không có Page/modal tương ứng | Không có | Thiếu toàn bộ form đổi text/thay hoặc xóa media |
| Profile | `OLD/index.php:109` | `NEW/resources/js/Pages/Profile/Show.vue` | Khá | Thông tin/count thật, fallback banner chung; thiếu tab repost và nút DM; có block/mute |
| Sửa profile | `OLD/index.php` settings | `NEW/resources/js/Components/EditProfileModal.vue` | Trung bình | useForm POST thật; thiếu WEBP/GIF; lệch giới hạn; cần hiển thị lỗi đủ trường |
| Settings/đổi password | `OLD/index.php:291` | `NEW/resources/js/Pages/Profile/Edit.vue`, Partials/*, Layouts/AuthenticatedLayout.vue | Thấp | Route `/profile` có, nhưng template layout gọi `route('dashboard')` không tồn tại; sidebar chính không dẫn tới settings |
| Search | `OLD/index.php:69` | `NEW/resources/js/Pages/Search.vue` | Trung bình | Dữ liệu thật, Top/Latest/People; mất tìm replies, phân trang và lọc active |
| Hashtag | helpers::render_text, index explore | Search.vue + Tweet.vue | Thấp | Nhập # query được LIKE; bài viết không biến hashtag thành link; không có index hashtag |
| Explore/trending | `OLD/index.php:95` | `NEW/resources/js/Pages/Explore.vue` | Trung bình về bố cục | Bài thật take15; trend props giả; không có tiếp trang bài |
| Notifications | `OLD/index.php:229` | `NEW/resources/js/Pages/Notifications.vue` | Trung bình | List/badge thật; thiếu link bài, paging, mention/quote/message; GET mark toàn bộ |
| Bookmarks | `OLD/index.php:222` | `NEW/resources/js/Pages/Bookmarks.vue` | Khá | List thật và empty state; get toàn bộ; bỏ bookmark cập nhật icon nhưng chưa loại card khỏi list tại chỗ |
| Followers/following | `OLD/index.php:161` | `NEW/resources/js/Pages/Profile/FollowList.vue` | Khá | Dữ liệu thật/toggle thật; thiếu sắp pivot mới nhất và giới hạn/phân trang |
| Sidebar/trending/gợi ý | `OLD/app/view.php::page_header/page_footer` | `NEW/resources/js/Layouts/TwitterLayout.vue`, MenuItem.vue | Trung bình | User/badge thật, search thật; trend viết cứng; thiếu who-to-follow, Messages inert, không Settings |
| Tin nhắn | `OLD/index.php:250` | Chưa có Page | Không có | Thiếu hộp thư, lịch sử/seen/preview/unread |
| Report bài/user | `OLD/index.php:317` | Components/Tweet.vue report modal; Profile/Show.vue | Trung bình | Report bài nối thật; report tài khoản chỉ backend; chuyển lý do tự do thành lựa chọn |
| Admin | `OLD/index.php:333` | `NEW/resources/js/Pages/Admin/{Dashboard,Reports,Users}.vue` | Khá, mở rộng | Props thật, filter/action thật; cascade làm mất report, filter không giữ khi paging; lỗi action cần feedback rõ |
| Modal/confirm | `OLD/assets/js/app.js:2` dùng confirm; quote/sửa là trang | Components/Tweet.vue, EditProfileModal.vue, TwitterLayout.vue | Thiết kế khác | Modal custom thiếu chuẩn focus/Escape/aria đồng nhất; Modal.vue dùng cho delete account có logic focus riêng |
| Responsive | `OLD/assets/css/style.css` breakpoints1200/940/620; mobile-nav | TwitterLayout.vue:146,262,307 và các Tailwind classes | Có xử lý, khác điều hướng | Mới ẩn rightbar ở màn nhỏ, giữ sidebar60px; chưa có bottom-nav như cũ. Chưa kiểm thử màn320/375/tablet hoặc overflow |
| Dark mode | `OLD/app/view.php` color-scheme dark; CSS variables | TwitterLayout.vue và Pages dark classes; Profile/Edit.vue nền sáng | Cùng định hướng tối | Cả hai không có toggle light/dark được nối; bản mới settings/auth layout khác màu, không coi đó là tính năng chuyển theme |

### 2.2 Bảng dữ liệu giả, nút chưa nối và placeholder

| Loại | File/dòng/đoạn mới | Cái gì giả/chưa nối | Nối với đâu hoặc xử lý |
|---|---|---|---|
| 🧪 | `NEW/app/Http/Controllers/SearchController.php:83–109` | 5 topics và số 42.8K/28.1K/19.4K/11.5K/35.2K cố định | Hashtag/pivot + truy vấn đếm bài7ngày; cung cấp Explore props |
| 🧪 | `NEW/resources/js/Layouts/TwitterLayout.vue:331–346` | Ba topic “Trending” tĩnh và mô tả công nghệ | Dùng cùng nguồn trends của Explore, có empty state |
| ⚠️ | `NEW/resources/js/Layouts/TwitterLayout.vue:157`; `Components/MenuItem.vue` nhánh v-else | Messages nhìn như menu nhưng không href/click | Route/messages + inbox; khi chưa làm không trình bày như nút đã hoạt động |
| ⚠️ | `NEW/resources/js/Layouts/TwitterLayout.vue:450–454` | Icon GIF/emoji có cursor-pointer, không handler | Nối picker thật hoặc bỏ affordance; đây không phải yêu cầu có trong bản cũ |
| ⚠️ | `NEW/resources/js/Layouts/TwitterLayout.vue:395–398,437` | Everyone kèm chevron, Everyone can reply là tĩnh | Nếu chỉ công khai thì dùng nhãn; chỉ tạo selector khi có privacy/reply policy |
| ⚠️ | `NEW/resources/js/Layouts/AuthenticatedLayout.vue:23,32,115` | Link gọi route dashboard không khai báo | Đổi sang route hiện có, thống nhất Settings layout; đây là lỗi route, không phải dữ liệu mock |
| Placeholder hợp lệ | `NEW/app/Models/User.php:92,106`; `Components/Tweet.vue:323` | Avatar fallback theo tên, banner Unsplash chung | Không phải hồ sơ giả; nên có fallback cục bộ và kiểm soát phụ thuộc mạng |
| Code dư | `NEW/resources/js/Layouts/TwitterLayout.vue:63–64` | randImg1/randImg2 từ picsum + Math.random, không dùng trong template đọc được | Xóa code dư ở giai đoạn sửa; không tính thành một luồng mock đang hiển thị |
| Fixture hợp lệ | `NEW/database/seeders/TweetsSeeder.php::run` | Tên demo, avatar URL, bài viết mẫu | Được lưu DB và tương tác thật, không tính là dữ liệu runtime hardcode; giới hạn seed vào môi trường demo |
| Scaffolding dư | `NEW/resources/views/welcome.blade.php` | Trang Laravel mặc định còn trong nguồn | Route `/` render Inertia Welcome, không kết luận template này là màn production |

Không tìm thấy TODO/FIXME/console.log/lorem/mock/hardcode trong phạm vi `NEW/app`, `resources`, `routes`, `database` đã tìm (template welcome mặc định loại riêng). Không dùng “không thấy TODO” để suy ra hoàn thiện: những chỗ mock bên trên không được đánh dấu bằng từ khóa. Các `v-html="link.label"` tìm được ở Admin/Users.vue và Admin/Reports.vue là nhãn pagination do Laravel sinh, không phải nội dung tweet người dùng.

### 2.3 UX cụ thể

| Khía cạnh | Hiện có | Vấn đề và bằng chứng |
|---|---|---|
| Loading | Inertia progress trong `resources/js/app.js`; Welcome::isSubmitting/isLoadingMore; EditProfileModal form.processing | Nhiều toggle axios không khóa khi request pending; click nhanh có thể đưa optimistic state về kết quả request đến trễ (`Components/Tweet.vue:83–155`) |
| Empty | Home, Search, Explore, Profile, Bookmarks, Notifications và FollowList có v-if/empty copy | Không có empty “trends từ DB” vì trends hiện luôn là mảng giả (`SearchController::explore`) |
| Error | Welcome hiển thị errors.tweet/file; card có toast rollback; auth có InputError | Profile/Show::toggleBlock/toggleMute nuốt lỗi trong catch; follow rollback không giải thích. CheckSuspended trả JSON thô cho Inertia, có thể hiện modal lỗi thay vì lỗi form nhất quán |
| Feedback | Like/repost/bookmark optimistic, reply/quote toast, admin flash | Toast quote cập nhật sai count; copyShareLink báo Copied trước khi Promise hoàn tất; Tweet.vue ref từ props không watch nên prop refresh có thể không đồng bộ local count |
| Pagination | Home paginate15 + Show more; admin render pagination | Home không có IntersectionObserver nên README gọi infinite scroll là chưa chính xác. Search và Notifications nhận paginator nhưng không render controls; Profile/Bookmark/Follow get không giới hạn; Explore take15 |
| Tích lũy home | Welcome.vue watch props, append khi page>1, dedupe tweet.id | Khi thêm activity repost cần activity key; khi xóa bài đã tải ở trang trước, append-only có thể giữ card cũ. Đây là suy luận từ watcher, chưa tái hiện browser |
| Client validation | Auth useForm, home280 counter, server FormRequest | Cũ dùng Array.from đếm code point; mới `.length` UTF-16 có thể từ chối emoji sớm hơn backend. Reply/quote modal không giới hạn nhập280 ở textarea, báo lỗi chung; file picker thiếu size validation sớm (`Tweet.vue::submitReply/submitQuote`, TwitterLayout::getFile) |
| Media preview | Home hỗ trợ preview, modal reply/quote có img | Reply/quote dùng img dù accept video/mp4; modal global chỉ mp4 là video, khác backend mov/webm. URL.createObjectURL không thấy revoke trong các handler mới, cũ có revoke |
| Accessibility | Auth labels/input; avatar một số alt; generic Modal.vue có focus handling | Card clickable div không có keyboard navigation tương đương; nhiều icon button không aria-label; custom modal thiếu role dialog/aria-modal/focus trap/Escape; toast thiếu aria-live (`Tweet.vue`, TwitterLayout.vue, EditProfileModal.vue) |
| Ngôn ngữ/nhất quán | Admin phần lớn tiếng Việt, mạng xã hội tiếng Anh | Bản cũ tiếng Việt thống nhất; `lang/en` và các chuỗi hardcoded mới nên thống nhất trước demo |

## Phần 3 — Kiến trúc và tiêu chuẩn hiện đại

### 3.1 Bảng điểm

Chấm 13 tiêu chí, trọng số bằng nhau. 0 = chưa có, 5 = có nền tảng nhưng còn rủi ro đáng kể, 8 = đầy đủ có kiểm chứng, 10 = rất tốt và vận hành/kiểm thử có bằng chứng. Không cộng điểm chỉ vì nhiều thư mục; không ép Inertia phải tách API như cloud-project.

| Hạng mục | twitter-clone hiện có | cloud-project thực sự có | Điểm /10 | Cần bổ sung |
|---|---|---|---:|---|
| Migrations | U/T/I/F/N/R có FK, unique interaction và follow, cascade reply, null quote; thiếu messages/hashtag; R cascade sai; không thấy index feed tổng hợp | BE migration files có index(user_id,folder_id,file_extension), display_name; version/share và trigger storage | 6 | Hoàn thiện schema parity, set-null report, index dựa truy vấn; counter hiện dùng withCount, không cần thêm cột counter chỉ để đủ checklist |
| Eloquent | `Models/Tweet.php` scopeWithInteractionDetails, quan hệ, casts, accessor, deleting closure; User nhiều quan hệ; không có Observer class riêng | `BE/app/Models/File.php` fillable/casts/SoftDeletes/quan hệ; FileRepository eager versions | 7 | Kiểm soát serialization, cleanup cascade, scope visibleTo; closure hiện tại là model event hook, README gọi Observer chưa chính xác |
| Validation | 3 FormRequest: StoreTweetRequest, ProfileUpdateRequest, Auth/LoginRequest; register/profile/report/admin validate inline | 33 tệp Request theo tính năng, BaseFormRequest, UploadFileRequest | 6 | Request cho update profile/report/admin; contract cũ; URL scheme, query bounds, parent/quote loại trừ |
| Auth/phân quyền | Session web, CSRF, login limiter, delete policy, admin/suspended middleware | Sanctum API token, Gate admin, Request/Service ownership; `BE/routes/api.php`, AppServiceProvider | 6 | Suspended đồng nhất, policies interaction/block, email verification nếu công bố có, throttle mutation. Không cần Sanctum API chỉ vì đã cài package |
| Hiệu năng | Tweet scope eager user/quote + withCount/withExists tốt; feed paginate15; N+1 và get-all còn nhiều | FileRepository::paginateForUser, getWithLatestVersion; schema index và các Service | 5 | withExists follow, paginate các trang, giới hạn notifications update, trend cache khi có query thật |
| Tổ chức code | Controllers theo chức năng; TweetPolicy, Notification; không Services/Actions/Resources riêng; AdminDashboardController234 dòng | 11 Services/4 Repositories; BaseApiController, exception/response helpers, docs theo feature | 6 | Action/service cho tạo/xóa bài, media, moderation; DTO/Resource public; không bắt tạo layer rỗng cho toggle đơn giản |
| Bảo mật | Fillable, whitelist create, hash, CSRF, Vue escape; website không URL policy; public User serialized quá rộng; upload profile chưa thống nhất storage | BE có Gate/ownership và lỗi chuẩn; .env.example lại có APP_KEY, DEV_REPORT; không phải chuẩn bảo mật tuyệt đối | 5 | Public payload tối thiểu, HTTP(S), kiểm soát status/block; upload dựa MIME nhất quán, không công khai khóa thật |
| Seeder/Factory | 2 factory, 3 seeder; 5 user + tweets/reply/quote/follow/like/bookmark/notification; AdminSeeder không được DatabaseSeeder gọi | 2 factory, 6 seeder; DatabaseSeeder gọi user/config/folder/file/version | 7 | Admin demo khi setup, reports/suspended/edge cases, đủ dữ liệu pagination; seed idempotent |
| Kiểm thử | 14 Feature test files, 1 Unit, tổng67 test methods; 95 PHP files lint đạt; chưa chạy suite | Chỉ 1 Feature +1 Unit mẫu, 72 HTTP sample; FE có trang thử API | 6 | Ưu thế automated test hơn cloud; thiếu admin/report/mention/messages/repost-feed/security/UI; môi trường test tách biệt |
| Chuẩn code | Composer có Pint, `.editorconfig`, PSR-4; không thấy lint script JS, PHPStan/Larastan, Prettier config | BE Pint; FE ESLint/TypeScript và script lint | 5 | Pint --test, ESLint Vue, format, static analysis có baseline hợp lý |
| Tài liệu | README riêng có cài đặt/seed/test/demo; `.env.example`; chưa có PROJECT_DOCS/ERD/screenshot/CONTRIBUTING/LICENSE riêng | PROJECT_DOCS, AUTH_API, OpenAPI, docs API/user/admin, MDX component; README backend phần lớn template | 6 | Sửa tuyên bố production/realtime/full-text/infinite-scroll; thêm architecture/ERD/use cases/ảnh/license nguồn gốc |
| DevOps | Sail dependency, Vite build+SSR; .env.example; không thấy Docker compose/workflow CI | Sail dependency, FE vercel.json; không thấy workflow hoặc Docker compose riêng | 4 | CI lint/test/build, test DB an toàn, quy trình deploy/migrate/backup; Docker/Sail cấu hình tái lập nếu cần |
| Frontend | Vue3/Inertia, Page/Layout/Component, useForm, optimistic state, Vite SSR config | React/TS API layer, Query, Router, Redux uploads, reusable components/MDX | 7 | Tách shared composables/composer/card, state sync/pagination/error/a11y; store toàn cục chỉ khi thực sự cần |
| **Tổng** | | | **76/130** | **5,85/10** |

### 3.2 Các phát hiện kiến trúc/bảo mật có thể hành động

| Mã | Phát hiện, bằng chứng | Tác động và kết luận có giới hạn |
|---|---|---|
| A01 | `NEW/app/Models/User.php:52` chỉ hidden password/remember_token; `UserProfileController::show:68` trả User đầy đủ; `Tweet::scopeWithInteractionDetails:79` eager user không select; FollowController/SearchController trả User | Email, birthdate và thuộc tính không cần công khai có thể nằm trong Inertia JSON dù UI không hiển thị. Đây là luồng serialization thấy được trong mã, chưa capture HTTP; cần public resource/allowlist |
| A02 | `NEW/app/Http/Controllers/UserProfileController.php:89` website string; `Profile/Show.vue:267` dùng thẳng href | Không bảo đảm HTTP(S) như cũ. Vue escape HTML không thay thế validate URL scheme. Chưa thử payload browser; không khẳng định đã khai thác XSS |
| A03 | `FollowController::toggle`, `TweetController::show/store`, Like/Retweet/BookmarkController không kiểm tra block; Search/Explore không áp excluded users | Block chỉ có tác dụng home/profile. Nếu sản phẩm hứa chặn tương tác/truy cập, người bị block vẫn có đường gọi trực tiếp. Cần định nghĩa quyền xem guest và áp policy nhất quán |
| A04 | LoginRequest::authenticate không status; routes auth/admin ngoài check.suspended; CheckSuspended chỉ chặn non-GET | Bị đình chỉ vẫn login/đọc notifications và gọi password.update. GET notifications còn ghi read_at. Phải giữ đúng trạng thái mất đăng nhập của bản cũ hoặc ghi rõ thay đổi được chấp thuận |
| A05 | Migration R dùng cascade tweet_id/reported_user_id; AdminDashboardController::deleteTweetFromReport:119–127 | Report bị DB xóa trước khi lưu status/resolver, mất bằng chứng moderation. Sửa FK trước khi triển khai thao tác quản trị |
| A06 | StoreTweetRequest::withValidator:39 dựa extension gốc; TweetController::store:109 phân loại video bằng extension gốc | MIME hợp lệ có thể đi nhầm nhánh size/type khi đổi tên extension; giới hạn ảnh không được quyết định hoàn toàn từ MIME. `store()` tweet sinh tên storage, điểm này tốt |
| A07 | UserProfileController::update dùng getClientOriginalExtension, public/pics, timestamp; không xóa file cũ/transaction; TweetController::store lưu file trước create | Nguy cơ file rác, collision cùng giây, đường lưu không thống nhất; chưa chứng minh thực thi mã. Đã đọc `vendor/laravel/framework/src/Illuminate/Validation/Concerns/ValidatesAttributes.php::validateMimes/shouldBlockPhpUpload`: Laravel chặn các extension PHP phổ biến, nên không kết luận đơn giản rằng upload .php sẽ qua |
| A08 | Tweet::booted chỉ deleting của model hiện tại; T/U foreign keys cascade; ProfileController::destroy xóa User | DB cascade không chạy event deleting cho từng tweet/reply; ảnh của reply/tài khoản có thể mồ côi. Seed dùng `/pics/Code.png`/video dùng chung, hook unlink public asset có thể xóa tài nguyên được nhiều seed row tham chiếu |
| A09 | User fillable có role/status; các controller hiện tại gán field có chọn lọc | Có bề mặt rủi ro nếu sau này dùng request->all(), nhưng **chưa thấy đường mass assignment nâng quyền trực tiếp** trong các handler đã đọc. Không ghi như lỗ hổng đã khai thác |
| A10 | TweetController::show ghi TweetView trên GET, check recent rồi create, không ràng buộc unique theo window | Race có thể tạo nhiều view, dữ liệu IP/session tăng không giới hạn; thêm retention và chiến lược dedup phù hợp quy mô, không thêm unique vĩnh viễn làm sai cửa sổ2giờ |
| A11 | Migration add user_id non-null + drop columns cũ; composer dev có doctrine/dbal4 | Schema nâng từ DB tweets mẫu có dữ liệu cần backfill/chuyển đổi. Tương thích migrate/rollback thực tế chưa xác định, không chạy migration chỉ để kiểm tra trong giai đoạn đọc |

Không thấy SQL raw chứa đầu vào người dùng trong controllers mới đã đọc; `where('tweet','like', "%{$query}%")` vẫn được Eloquent bind tham số, không phải bằng chứng SQL injection. `whereRaw('1 = 0')` là hằng. Tuy vậy q chưa validate scalar/độ dài và wildcard `%/_` có thể làm truy vấn rộng. Không thấy khóa dịch vụ thật trong các tệp nguồn/config example đã kiểm tra của bản mới; không thể kết luận toàn bộ lịch sử Git/.env không có secret vì chúng ngoài phạm vi.

### 3.3 N+1 và truy vấn cụ thể

| Vị trí | Số truy vấn/phạm vi suy ra từ mã | Sửa đề xuất |
|---|---|---|
| `NEW/app/Http/Controllers/FollowController.php:57–63,80–86` | Sau get(), map gọi `$u->followers()->...->exists()` cho mỗi người khi đã login: thêm N query; withCount không giải quyết exists này | withExists có constraint viewer, select public columns và paginate |
| `NEW/app/Http/Controllers/SearchController.php:33–37` | Trang People15 người thêm tối đa15 exists query | Đưa is_following vào withExists của query ban đầu |
| `NEW/app/Http/Controllers/NotificationController.php:25,38` | Lấy toàn bộ unread collection rồi markAsRead theo model; số update tăng theo unread, ngoài20 record paginator | Mass update theo user khi bấm nút; nếu cần model event thì chunk/job có lý do rõ |
| `NEW/app/Http/Controllers/UserProfileController.php::show`, `BookmarkController::index`, `TweetController::show` replies | Không nhất thiết N+1 nhờ scope eager; vấn đề chính là get-all và tải payload quá lớn | paginate/cursor, có UI controls, giữ thứ tự đúng contract |
| `NEW/app/Models/Tweet.php::scopeWithInteractionDetails` | Eager user/quoteOf.user + withCount/withExists tránh truy vấn per-card cho tương tác | Giữ cách này, giới hạn user columns; withCount chưa có bookmarks khiến detail `bookmarks_count ||0` không có tổng ban đầu |
| `NEW/app/Http/Controllers/Admin/AdminDashboardController.php::index/formatReport` | index eager reporter/tweet.user/reportedUser nhưng formatReport truy cập resolver; pending thường null, nên chưa chứng minh N+1 thực tế | Eager resolver nếu các row có resolved_by; không ghi đây là lỗi đã đo |

Chưa có EXPLAIN/profiler nên không nêu millisecond hoặc throughput giả. Đề xuất index tweets(parent_id,created_at,id), tweets(user_id,created_at,id), report(status,created_at), notification(notifiable_type,notifiable_id,read_at) phải kiểm tra plan/thực tế trước thêm; các FK/unique đã tạo index một phần. Search `%term%` không tự trở thành full-text chỉ nhờ index thường.

### 3.4 Kiểm thử và xác minh đã thực hiện

| Việc | Kết quả |
|---|---|
| PHP CLI | `C:/xampp/php/php.exe -v`: PHP8.2.12 |
| Kiểm tra syntax | Chạy `php -l` từng tệp PHP trong `NEW/app`, `routes`, `database`, `tests`: **95 tệp, 0 lỗi**; không bootstrap app |
| Đếm suite | **67 phương thức test_**, **14 tệp Feature +1 Unit**, cùng TestCase/CreatesApplication. README ghi67 khớp số phương thức; không phải67 kết quả pass |
| `php artisan test` | **Không chạy** vì ràng buộc chỉ đọc. `NEW/phpunit.xml` SQLite memory đang comment; test dùng RefreshDatabase có thể chạm DB cấu hình hiện tại. `tests/Feature/ProfileAndFollowTest.php:225` POST ảnh vào handler move public/pics rồi xóa; TimelineAndStorageTest dùng Storage::fake ghi storage/framework/testing; PHPUnit/app còn có thể ghi cache/views/logs |
| Build/Pint | Không chạy build tạo public/build/bootstrap SSR hoặc Pint sửa file. Chưa xác định build/pint có pass |
| Kiểm tra browser/HTTP/DB | Chưa thực hiện, do trang GET cũng có side effects. UI đánh giá bằng template/controller |
| cloud-project | Đã đọc 2 ExampleTest; chưa chạy suite. 72 file HTTP và trang thử API là công cụ test tay, không suy ra coverage tự động |

**Độ phủ ước lượng:** theo số nhóm có test (auth, tweet, interaction, profile/follow/block/mute, timeline/storage, notification, search), bản mới có bề rộng nghiệp vụ tự động tốt hơn hai test mẫu của cloud. Không có coverage report để tính % dòng/nhánh; **chưa xác định được**. Test hiện tại còn xác nhận một số hành vi lệch cũ (GET notifications mark read trong NotificationTest), do đó pass suite tương lai cũng chưa đủ xác nhận parity. Không có test riêng messages/hashtag/mention/admin/report; không có E2E frontend nên lỗi route dashboard có thể không bị PHP feature test phát hiện.

Khi được giao giai đoạn sửa, cấu hình test DB riêng có kiểm tra an toàn, chuyển upload profile sang Storage::disk để fake, redirect đường storage/log/view vào môi trường test rồi chạy suite. Không dùng nguyên `php artisan test` trên cấu hình hiện tại trước bước cách ly.

### 3.5 Tài liệu và khoảng cách lớn nhất

README bản mới có ích cho setup nhưng cần sửa các phát biểu sau: “production-grade” chưa có kiểm chứng; “full-text” thực tế là LIKE (`SearchController::index`); “real-time” hiện Notification chỉ channel database (`AppActivityNotification::via`); “infinite scroll” là nút Show more (`Welcome.vue::loadMore`); “Observer” thực tế closure model hook; “automated media cleanup” không bao phủ cascade và lỗi ghi DB. Có license MIT ở composer metadata, không thấy LICENSE độc lập/giải thích attribution cho nguồn clone trong README.

Top khoảng cách so với phần tốt của cloud-project:

1. Thiếu use-case/service cho tạo bài, media và moderation có transaction (`BE/FileService::upload` so với `NEW/TweetController::store`).
2. Request chưa đồng đều (33 tệp phía BE so với3 mới); tránh đổi thành Request chỉ để tăng số lượng.
3. Thiếu hợp đồng dữ liệu public/response nhất quán, dễ gửi User đầy đủ (`BE/FileController::store` payload có chọn trường).
4. Thiếu tài liệu feature/API/ERD có độ sâu tương tự `BE/PROJECT_DOCS.md`, `AUTH_API.md`, `docs/`.
5. Frontend chưa tách reusable interaction/composer và error/state như `FE/src/api`, `src/state`, `src/components`.
6. Thiếu ESLint/type checking như `FE/package.json`, `eslint.config.js`, `tsconfig.app.json`.
7. Chưa có demo admin/report hoàn chỉnh qua seeder mặc định, trong khi BE seed có user/config/folder/file/version.
8. Cần quy trình build/deploy tái lập; FE có vercel rewrite, nhưng cả hai vẫn thiếu bằng chứng CI/Docker riêng.

**Điểm không phải khoảng cách:** số PHPUnit test nghiệp vụ của bản mới vượt cloud; Inertia monolith phù hợp đề tài, không cần chuyển React/API; Policy/withExists hiện có nên không đưa “tạo từ đầu” vào checklist.

## Phần 4 — Kế hoạch hành động

Các file ở phần này là **đề xuất sửa/tạo ở giai đoạn tiếp theo**, chưa thực hiện. Tiền tố `NEW/` có nghĩa đường dẫn đầy đủ trong bảng đầu báo cáo. Ước lượng cho một người quen Laravel/Vue; bao gồm regression test sát thay đổi trong từng mục P0/P1, chưa gồm25giờ hạ tầng/tài liệu P2 và23giờ dự phòng.

### 🔴 P0 — Hoàn thiện yêu cầu gốc, sửa lỗi chặn demo và bảo mật

- [ ] **P0-01 — `NEW/resources/js/Layouts/AuthenticatedLayout.vue`, `Layouts/TwitterLayout.vue`, `Pages/Profile/Edit.vue`; `NEW/phpunit.xml`, `tests/CreatesApplication.php`:** sửa tham chiếu dashboard và thêm đường vào Settings; chuẩn bị DB/storage test cách ly, xác minh `/profile` bằng frontend khi sang giai đoạn sửa | Lý do: khôi phục đổi mật khẩu và tránh suite chạm DB demo | **Dễ/vừa · 4 giờ · Không phụ thuộc**.
- [ ] **P0-02 — `NEW/app/Http/Requests/Auth/LoginRequest.php`, `Auth/RegisteredUserController.php`, `Middleware/CheckSuspended.php`, `routes/auth.php`, `routes/web.php`, `Policies/TweetPolicy.php`, `FollowController.php`; tạo `NEW/app/Http/Resources/PublicUserResource.php`:** khôi phục username/email login, contract register, status active; áp policy block/view/interaction; allowlist user public; bổ sung regression chống lộ email/birthdate và bypass suspended | Lý do: phân quyền và riêng tư đang không đồng nhất | **Khó · 8 giờ · P0-01**.
- [ ] **P0-03 — `NEW/app/Http/Controllers/UserProfileController.php`, `Requests/StoreTweetRequest.php`; tạo `NEW/app/Http/Requests/UpdateUserProfileRequest.php`, `NEW/app/Services/MediaService.php`; sửa `NEW/app/Models/Tweet.php`:** website HTTP(S), giới hạn theo cũ; phân loại/size theo MIME, tên sinh an toàn, Storage public, cleanup file lỗi/ảnh cũ/cascade; phân biệt asset seed dùng chung với upload | Lý do: dữ liệu không hợp lệ và vòng đời media | **Vừa/khó · 8 giờ · P0-01**.
- [ ] **P0-04 — `NEW/app/Http/Controllers/TweetController.php`, `routes/web.php`, `Policies/TweetPolicy.php`; tạo `NEW/app/Http/Requests/UpdateTweetRequest.php`, `NEW/resources/js/Components/EditTweetModal.vue`; sửa `Components/Tweet.vue`:** thêm edit text/thay/xóa ảnh của chính chủ, cấm parent+quote đồng thời, transaction và rollback; định nghĩa rõ redirect sau đăng | Lý do: thiếu chức năng gốc edit và sai quy tắc post | **Vừa · 8 giờ · P0-02,P0-03**.
- [ ] **P0-05 — tạo `NEW/app/Services/TimelineService.php`; sửa `TweetController.php`, `UserProfileController.php`, `resources/js/Pages/Welcome.vue`, `Pages/Profile/Show.vue`, `Components/Tweet.vue`:** feed theo activity gồm repost, sort feed_at/id, key theo activity; thêm tab repost profile; bỏ tăng retweet count khi quote; nối pagination reply/bookmark/profile/follow/search/notification/explore và link cha/notification đích | Lý do: giữ luồng đọc và không giấu kết quả sau trang đầu | **Khó · 12 giờ · P0-02,P0-04**.
- [ ] **P0-06 — tạo `NEW/database/migrations/*_create_messages_table.php`, `NEW/app/Models/Message.php`, `NEW/app/Http/Controllers/MessageController.php`, `NEW/app/Http/Requests/SendMessageRequest.php`, `NEW/app/Policies/MessagePolicy.php`, `NEW/resources/js/Pages/Messages/Index.vue`; sửa routes/layout/notification:** nhắn1-1, active target khác mình,1–2000ký tự, contacts/preview/history/seen/unread badge, chỉ hai bên đọc được; notification dẫn đúng hội thoại | Lý do: ba luồng cũ hoàn toàn chưa có | **Khó · 16 giờ · P0-02**.
- [ ] **P0-07 — tạo `NEW/database/migrations/*_create_hashtags_tables.php`, `NEW/app/Models/Hashtag.php`, `NEW/app/Services/PostTextService.php`, `NEW/resources/js/Components/PostText.vue`; sửa TweetController/SearchController/AppActivityNotification/TwitterLayout:** Unicode tag normalization, sync lúc tạo/sửa, link hashtag/mention an toàn, mention dedup/exclude, quote notification, trend7ngày và who-to-follow thật, giữ notification unread đến POST | Lý do: thay dữ liệu giả và khôi phục nghiệp vụ text/discovery | **Vừa/khó · 8 giờ · P0-04,P0-05**.
- [ ] **P0-08 — tạo `NEW/database/migrations/*_preserve_report_targets.php`; sửa `NEW/app/Http/Controllers/Admin/AdminDashboardController.php`, `ReportController.php`, `resources/js/Pages/Profile/Show.vue`, `Pages/Admin/Reports.vue`:** FK set-null cho target, giữ report sau delete; transaction hợp lý, bảo toàn ghi chú/status, UI report user, lý do5–500, giữ filters khi paging; test xóa bài vẫn còn lịch sử | Lý do: chức năng moderation hiện có nguy cơ mất bằng chứng | **Vừa · 6 giờ · P0-01,P0-02,P0-03**.

**P0 tổng:70giờ.** Thứ tự triển khai:01→02→03→04→05→06→07→08; có thể đưa08 ngay sau03 nếu chuẩn bị demo admin trước.

### 🟠 P1 — Chuẩn kiến trúc, hiệu năng, dữ liệu demo và UX

- [ ] **P1-01 — tạo `NEW/app/Actions/CreateTweet.php`, `DeleteTweet.php`, `ResolveReport.php`; tạo các `NEW/app/Http/Requests/*ReportRequest.php`, `*Admin*Request.php`; sửa controllers hiện có:** gom orchestration/transaction, dùng validated input và resource, dùng lại TweetPolicy; không tạo Repository wrapper không cần thiết | Lý do: controllers đang gánh nghiệp vụ và input/output chưa đồng nhất | **Vừa · 6 giờ · P0-04,P0-08**.
- [ ] **P1-02 — `NEW/app/Http/Controllers/FollowController.php`, `SearchController.php`, `NotificationController.php`; tạo migration index phù hợp:** thay per-user exists bằng withExists, mass mark read theo user, kiểm tra query count và EXPLAIN; giới hạn query/search, cache trend có invalidation | Lý do: N+1 và tải không giới hạn | **Vừa · 5 giờ · P0-05,P0-07**.
- [ ] **P1-03 — `NEW/database/seeders/{DatabaseSeeder,TweetsSeeder,AdminSeeder}.php`, `database/factories/`; tạo fixture reports/messages:** seed đủ30+post, >20notifications, suspended/report/DM/hashtag, admin demo qua lệnh chuẩn; tránh nhân đôi dữ liệu mỗi lần seed, chỉ dùng credential demo trong local/testing | Lý do: trình diễn được mọi luồng và phân trang | **Vừa · 5 giờ · P0-06,P0-07,P0-08**.
- [ ] **P1-04 — tạo `NEW/resources/js/Composables/useTweetInteractions.js`, `Components/TweetComposer.vue`; sửa custom modals, Profile/Show.vue, Tweet/Show.vue:** đồng bộ props/local state, khóa request, lỗi cụ thể, clipboard await, revoke object URL, preview video đúng, keyboard/focus/aria-live; bỏ icon giả | Lý do: UX chưa thống nhất và code lặp | **Vừa · 4 giờ · P0-04,P0-05**.

**P1 tổng:20giờ.** Đây là refactor tập trung, không viết lại frontend hoặc chuyển framework.

### 🟡 P2 — Kiểm thử đầy đủ, chuẩn code, CI và tài liệu

- [ ] **P2-01 — `NEW/tests/Feature/`, `tests/Unit/`; tạo bộ E2E frontend và cấu hình runner trong `NEW/package.json`:** bổ sung toàn bộ acceptance40 hàng, permission matrix, report cascade, feed repost, pagination, upload failure, migration clean/upgrade, concurrency toggle; chạy PHP suite và UI smoke trong DB test riêng; ghi kết quả thật | Lý do:67test hiện tại chưa phủ parity hoặc browser | **Vừa · 8 giờ · P0 hoàn tất,P1-04**.
- [ ] **P2-02 — `NEW/composer.json`, `package.json`; tạo `NEW/pint.json`, cấu hình ESLint Vue, `NEW/phpstan.neon`:** lệnh lint không ghi file, định dạng dự án, static analysis tăng dần; sửa lỗi thực thay vì ignore toàn bộ | Lý do: thiếu kiểm tra chuẩn code có thể lặp lại | **Vừa · 3 giờ · P1-01**.
- [ ] **P2-03 — tạo `NEW/.github/workflows/ci.yml`:** cài dependencies theo lock, PHP lint/Pint/static analysis/tests với DB test, npm build/E2E smoke; artifact test report, không dùng secret/demo DB production | Lý do: chưa có CI riêng | **Vừa · 4 giờ · P2-01,P2-02**.
- [ ] **P2-04 — sửa `NEW/README.md`; tạo `NEW/PROJECT_DOCS.md`, `NEW/docs/ERD.md`, `NEW/docs/architecture.md`, `NEW/docs/screenshots/`, `NEW/LICENSE`, `NEW/CONTRIBUTING.md`:** ghi use case/schema/luồng quyền/cài đặt/test/demo/admin, ảnh sau verify, giới hạn còn lại; xác minh license/attribution trước chọn nội dung LICENSE; bỏ tuyên bố chưa đúng | Lý do: chênh tài liệu lớn với BE tham khảo | **Vừa · 6 giờ · P0,P2-01**.
- [ ] **P2-05 — tạo `NEW/compose.yaml`, cấu hình Sail/Docker phù hợp; sửa `NEW/.env.example`, bổ sung hướng dẫn deploy:** PHP/DB/mail/storage symlink rõ, volume và backup, migrate theo môi trường, tắt debug khi deploy; xử lý quyết định giữ/nâng Laravel trong ticket riêng sau parity | Lý do: dependency Sail chưa phải môi trường chạy tái lập | **Vừa · 4 giờ · P2-03,P2-04**.

**P2 tổng:25giờ. Tổng làm việc:115giờ; dự phòng20%=23giờ; kế hoạch138giờ.** Ước lượng chưa bao gồm chuyển dữ liệu production từ PHP cũ; nếu cần migration dữ liệu thật phải khảo sát mapping users/password/posts/IDs/media riêng. Không tự đổi mật khẩu/hash hay reset DB.

### 4.1 Lộ trình đề xuất

Giả định một người có6giờ tập trung/ngày,5ngày/tuần; đây là ngày công tương đối, không phải lịch đã đặt.

| Tuần | Ngày công | Việc | Giờ mục tiêu |
|---|---|---|---:|
| 1 | Ngày1–5 | P0-01→04, bắt đầu05; ưu tiên login/settings/privacy/media | 30 |
| 2 | Ngày6–10 | Xong05, DM06, bắt đầu07 | 30 |
| 3 | Ngày11–15 | Xong07/08; P1-01→04 | 30 |
| 4 | Ngày16–20 | P2 test/lint/CI/docs/Docker, bắt đầu regression dự phòng | 30 |
| 5 | Ngày21–23 | 18giờ dự phòng còn lại: sửa lỗi kiểm thử, chạy clean install và rehearsal demo | 18 |

Đầu ra mỗi nhóm: diff có thể review, acceptance test đi kèm, kết quả test thật, danh sách hạn chế. Không triển khai production hoặc xóa DB cũ để chứng minh xong.

### 4.2 Prompt tiếp theo cho Claude Code

Các prompt sau dành cho **giai đoạn cho phép sửa bản mới**. Chỉ chạy từng nhóm, không giao “làm hết báo cáo” trong một lượt thiếu kiểm chứng.

**Prompt1 — nền chạy và an toàn:**

> Đọc C:/xampp/htdocs/twitter-clone/PROJECT_AUDIT.md. Thực hiện P0-01 và P0-02 chỉ trong twitter-clone; hai dự án tham khảo chỉ đọc. Trước khi chạy test, cách ly DB/storage để không ghi DB demo. Sửa route dashboard/settings, phục hồi login username hoặc email và status active theo action.php/app/auth.php bản cũ; giới hạn dữ liệu public User và quyền block. Giữ các chức năng mới hợp lệ. Thêm regression test và báo kết quả thật, không migrate:fresh lên DB hiện tại.

**Prompt2 — bài viết và media:**

> Thực hiện P0-03/P0-04 theo PROJECT_AUDIT.md. Thống nhất upload qua Storage, MIME/size, website HTTP(S), cleanup lỗi và ảnh cũ; thêm sửa bài chỉ chủ sở hữu, thay/xóa ảnh, cấm parent+quote đồng thời. Không xóa asset seed dùng chung. Chỉ sửa twitter-clone, viết test lỗi upload/rollback/quyền và chạy trong môi trường test cách ly.

**Prompt3 — feed và phân trang:**

> Thực hiện P0-05. Dùng OLD/app/models.php::timeline làm contract: bài gốc và mỗi hoạt động repost có feed_at/reposter riêng; following gồm hoạt động của mình và người theo dõi. Sửa key frontend theo activity, thêm tab repost, giữ reply/search/bookmark/profile pagination và sort như báo cáo. Sửa quote-count, link cha và link notification. Kiểm thử dữ liệu nhiều trang và nhiều người repost cùng bài.

**Prompt4 — tin nhắn:**

> Thực hiện P0-06. Tái hiện OLD/action.php send_message và OLD/index.php messages: gửi1–2000ký tự tới active user khác mình, chỉ hai bên xem, contacts/preview/history/seen/unread/message notification. Nối menu Messages thật. Viết permission tests và UI smoke; không bắt buộc WebSocket vì bản cũ không có realtime.

**Prompt5 — text, discovery và thông báo:**

> Thực hiện P0-07 theo helpers.php::sync_tags/notify_mentions/render_text và models.php::get_trends/get_suggestions của bản cũ. Tạo hashtag/pivot, sync lúc tạo/sửa, link text an toàn, mention dedup/exclude, quote notification, top6 tag7ngày và4 gợi ý thật; bỏ mock trend. Chỉ mark notifications khi POST, có phân trang/link đối tượng. Test Unicode, self-mention, mention trong reply/quote và cập nhật tag sau edit.

**Prompt6 — moderation:**

> Thực hiện P0-08. Giữ reports sau khi tweet/user đích bị xóa bằng migration mới, không sửa lịch sử migration đã triển khai một cách tùy tiện. Sửa deleteTweetFromReport transaction/lịch sử, thêm UI report user và validation theo báo cáo, giữ query filter qua pagination. Kiểm thử admin/user/suspended và report vẫn tồn tại sau delete. Không thao tác dữ liệu demo thật trong tests.

**Prompt7 — kiến trúc và demo:**

> Thực hiện P1-01→04 sau khi P0 đạt acceptance. Refactor Actions/Requests/public resources, bỏ N+1 bằng withExists, seeder đủ tình huống và idempotent, composables/composer dùng chung, error/a11y/media preview. Không chuyển Inertia sang React/API. So sánh query count trước/sau bằng test/profiler và báo số đo thật.

**Prompt8 — nghiệm thu:**

> Thực hiện P2-01→05: chạy đủ test an toàn, bổ sung E2E, lint/static analysis, CI, README/PROJECT_DOCS/ERD/ảnh/license attribution, Docker/Sail nếu phù hợp. Chỉ báo pass khi có kết quả chạy. Cập nhật ma trận40luồng và phần giới hạn trong PROJECT_AUDIT.md sau nghiệm thu. Không publish/deploy production trong nhiệm vụ này.

## Phụ lục A — Cây filesystem ba cấp đầy đủ

### D:\cloud-project

```text
.vscode/
  settings.json
cloud-storage-be/
  app/
    Exceptions/
    Http/
    Models/
    Notifications/
    OpenApi/
    Providers/
    Repositories/
    Services/
    Support/
  bootstrap/
    cache/
    app.php
    providers.php
  config/
    app.php
    auth.php
    cache.php
    cors.php
    database.php
    filesystems.php
    l5-swagger.php
    logging.php
    mail.php
    queue.php
    sanctum.php
    services.php
    session.php
  database/
    factories/
    migrations/
    seeders/
    .gitignore
  docs/
    admin/
    api/
    user/
    HDSD_Folder_Preview_Download.md
  public/
    .htaccess
    favicon.ico
    index.php
    ping.txt
    robots.txt
  resources/
    css/
    js/
    views/
  routes/
    api.php
    console.php
    web.php
  storage/
    api-docs/
    app/
    framework/
  tests/
    Feature/
    Unit/
    TestCase.php
  .editorconfig
  .env.example
  .gitattributes
  .gitignore
  artisan
  AUTH_API.md
  composer.json
  composer.lock
  cookies.txt
  package.json
  phpunit.xml
  PROJECT_DOCS.md
  README.md
cloud-storage-fe/
  public/
    vite.svg
  requirements/
    admin-dashboard_no_impl_success.md
    auth_success.md
    bulk_no_impl_success.md
    config_no_impl_success.md
    file__no_impl_success.md
    file-version_no_impl.md
    folder__no_impl_success.md
    public-link_no_impl.md
    search_success.md
    share.md
    stats_no_impl_success.md
    trash__no_impl_success.md
    user-dashboard_no_impl_success.md
    users__no_impl_success.md
  src/
    api/
    app/
    components/
    hooks/
    state/
    utils/
    index.css
    main.tsx
  .env.example
  .gitignore
  eslint.config.js
  feature.md
  index.html
  noop
  package-lock.json
  package.json
  README.md
  tailwind.config.ts
  tsconfig.app.json
  tsconfig.json
  tsconfig.node.json
  vercel.json
  vite.config.ts
```

### C:\xampp\htdocs\twitter-php-mysql

```text
app/
  .htaccess
  auth.php
  bootstrap.php
  db.php
  helpers.php
  models.php
  view.php
assets/
  css/
    style.css
  js/
    app.js
database/
  .htaccess
  schema.sql
tests/
  check_environment.php
  test_helpers.php
uploads/
  avatars/
    .gitkeep
  covers/
    .gitkeep
  posts/
    .gitkeep
  .htaccess
.htaccess
action.php
config.example.php
config.php
index.php
PROJECT_REVIEW.md
README.md
```

### C:\xampp\htdocs\twitter-clone

```text
app/
  Console/
    Kernel.php
  Exceptions/
    Handler.php
  Http/
    Controllers/
    Middleware/
    Requests/
    Kernel.php
  Models/
    Bookmark.php
    Like.php
    Report.php
    Retweet.php
    Tweet.php
    TweetView.php
    User.php
  Notifications/
    AppActivityNotification.php
  Policies/
    TweetPolicy.php
  Providers/
    AppServiceProvider.php
    AuthServiceProvider.php
    BroadcastServiceProvider.php
    EventServiceProvider.php
    RouteServiceProvider.php
  Support/
    ReservedUsernames.php
bootstrap/
  cache/
    .gitignore
    packages.php
    services.php
  ssr/
    assets/
    ssr.mjs
  app.php
config/
  app.php
  auth.php
  broadcasting.php
  cache.php
  cors.php
  database.php
  filesystems.php
  hashing.php
  logging.php
  mail.php
  queue.php
  sanctum.php
  services.php
  session.php
  view.php
database/
  factories/
    TweetFactory.php
    UserFactory.php
  migrations/
    2014_10_12_000000_create_users_table.php
    2014_10_12_100000_create_password_resets_table.php
    2019_08_19_000000_create_failed_jobs_table.php
    2019_12_14_000001_create_personal_access_tokens_table.php
    2023_01_25_134943_create_tweets_table.php
    2026_10_19_130234_add_profile_fields_to_users_table.php
    2026_10_19_130254_update_tweets_table_for_user_relationship.php
    2026_10_19_132938_add_thread_and_quote_to_tweets_table.php
    2026_10_19_132950_create_interactions_tables.php
    2026_10_19_134417_create_follows_and_blocks_tables.php
    2026_10_19_141826_create_notifications_table.php
    2026_10_24_000001_add_role_status_to_users_table.php
    2026_10_24_000002_create_reports_table.php
  seeders/
    AdminSeeder.php
    DatabaseSeeder.php
    TweetsSeeder.php
  .gitignore
lang/
  en/
    auth.php
    pagination.php
    passwords.php
    validation.php
public/
  pics/
    avatars/
    banners/
    Code.png
    PlayGuitar.png
  storage/
  videos/
    Seo.mp4
    Sportsman.mp4
  .htaccess
  favicon.ico
  index.php
  robots.txt
resources/
  css/
    app.css
  js/
    Components/
    Layouts/
    Pages/
    app.js
    bootstrap.js
    ssr.js
  views/
    app.blade.php
    welcome.blade.php
routes/
  api.php
  auth.php
  channels.php
  console.php
  web.php
storage/
  app/
    public/
    .gitignore
  framework/
    cache/
    sessions/
    testing/
    views/
    .gitignore
tests/
  Feature/
    Auth/
    ExampleTest.php
    InteractionTest.php
    NotificationTest.php
    ProfileAndFollowTest.php
    ProfileTest.php
    SearchAndExploreTest.php
    TimelineAndStorageTest.php
    TweetTest.php
  Unit/
    ExampleTest.php
  CreatesApplication.php
  TestCase.php
.editorconfig
.env.example
.gitattributes
.gitignore
.phpunit.result.cache
artisan
composer.json
composer.lock
jsconfig.json
package-lock.json
package.json
phpunit.xml
postcss.config.js
README.md
tailwind.config.js
vite.config.js
```

