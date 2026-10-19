# Twitter/X Clone - Production Full-Stack Application

A modern, secure, and production-grade Twitter/X clone built with **Laravel 9**, **Vue 3 (Composition API)**, **Inertia.js**, and **Tailwind CSS**.

---

## 🚀 Key Features

### 1. Authentication & User Profiles
- **Robust Authentication**: Laravel Breeze integration with email/password authentication, password confirmation, and secure session management.
- **Reserved Usernames**: Blocks reserved system routes (`admin`, `settings`, `explore`, `login`, `notifications`, etc.) with custom regex validation (`[A-Za-z0-9_]{3,15}`).
- **Customizable Profile**: Edit display name, bio (280 max), location, website link, birthdate, and upload custom avatar and banner cover images.
- **Profile Tabs**: Dedicated filtered views for **Posts**, **Replies**, **Media**, and **Likes**.

### 2. Tweets, Threads & Media Storage
- **Tweet Composition**: Post up to 280 characters with real-time character counter and auto-expanding composer.
- **Threaded Replies**: Native hierarchical parent-child reply system with complete conversation status views (`/{username}/status/{id}`).
- **Quote Tweets**: Embed and quote other tweets with live quoted previews.
- **Secure Media Storage**: Upload images (JPG, PNG, WebP, GIF up to 5MB) and videos (MP4, MOV, WebM up to 50MB) backed by Laravel's Public Storage disk (`Storage::disk('public')`).
- **Automated Media Cleanup**: Lifecycle model observer automatically deletes orphaned physical files from disk when a tweet is deleted.

### 3. Social Interactions & Views
- **Optimistic UI Updates**: Instant client-side state updates for Likes, Retweets, and Bookmarks with automatic rollback on network failure.
- **Bookmarks**: Save posts to a private bookmarks collection (`/bookmarks`).
- **Deduplicated View Tracking**: View counter with 2-hour session/IP/user deduplication window.

### 4. Social Graph (Follow, Block & Mute)
- **Follow System**: Follow/unfollow users with follower/following counters and follower list pages (`/{username}/followers` and `/{username}/following`).
- **Block & Mute Protection**:
  - Blocking a user immediately severs all bidirectional follow connections.
  - Blocked and muted users' tweets are automatically excluded from the viewer's timeline.
  - Profile page displays appropriate block notices.

### 5. Timelines, Feed Tabs & Pagination
- **Home Feed Tabs**:
  - **For You**: Global feed featuring all top-level posts, filtered against blocked/muted accounts.
  - **Following**: Curated timeline showing only posts from followed accounts.
- **Smooth Pagination**: Inertia-powered pagination with seamless infinite scroll append and "Show more posts" controls without page reloads.

### 6. Search, Explore & Activity Notifications
- **Full-Text Search (`/search`)**: Search across tweets and people filtered by **Top**, **Latest**, and **People** tabs.
- **Explore Page (`/explore`)**: Trending hashtags and topics (#TwitterClone, #Laravel, #VueJS, #InertiaJS, #TailwindCSS) with clickable filters.
- **Activity Notifications (`/notifications`)**:
  - Real-time notification dispatch when someone likes, reposts, replies to your post, or follows you.
  - Unread notification badge counter on the sidebar navigation.
  - "Mark all as read" capability.

---

## 🛠️ Technology Stack

- **Backend**: Laravel 9 (PHP 8.0+ / 8.1+)
- **Frontend**: Vue 3 (`<script setup>` Composition API)
- **Adapter**: Inertia.js (Monolithic Single Page Application)
- **Styling**: Tailwind CSS (Twitter Dark Mode Theme)
- **Asset Bundler**: Vite 4 (with SSR support)
- **Database**: MySQL
- **Testing**: PHPUnit / Laravel Feature & Unit Tests (67 automated tests)

---

## 🏁 Getting Started

### Prerequisites
- PHP >= 8.0.2 with PDO, OpenSSL, Mbstring, and Tokenizer extensions
- Composer
- Node.js & NPM
- MySQL Server (e.g. XAMPP)

### Installation

1. **Clone the repository**:
   ```bash
   git clone https://github.com/John-Weeks-Dev/twitter-clone.git
   cd twitter-clone
   ```

2. **Install PHP dependencies**:
   ```bash
   composer install
   ```

3. **Configure Environment**:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```
   *Update your `.env` database configuration:*
   ```ini
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=twitter-clone
   DB_USERNAME=root
   DB_PASSWORD=
   ```

4. **Connect Public Storage**:
   ```bash
   php artisan storage:link
   ```

5. **Run Migrations & Seeders**:
   ```bash
   php artisan migrate --seed
   ```

6. **Install Node Dependencies & Build Assets**:
   ```bash
   npm install
   npm run dev
   # or for production:
   npm run build
   ```

7. **Start Application**:
   ```bash
   php artisan serve
   ```
   Visit `http://localhost:8000` in your browser.

---

## 👤 Demo Accounts

The database seeder pre-populates several realistic accounts:

| Name | Username | Email | Password |
|---|---|---|---|
| **Demo User** | `@demouser` | `demo@example.com` | `password` |
| **Taylor Otwell** | `@taylorotwell` | `taylor@laravel.com` | `password` |
| **Evan You** | `@youyuxi` | `evan@vuejs.org` | `password` |
| **Elon Musk** | `@elonmusk` | `elon@x.com` | `password` |
| **John Weeks** | `@johnweeksdev` | `john@example.com` | `password` |

---

## 🧪 Automated Testing

Run the full automated test suite containing 67 feature & unit tests:

```bash
php artisan test
```

Test coverage includes:
- **Authentication**: Login, registration, password confirmation, email verification, password reset, reserved username protection.
- **Tweets**: Creation, text/media requirements, 280-char limit, authorization policy, deletion cascades.
- **Interactions**: Like/unlike toggle, retweet/undo toggle, bookmarking, reply threading, quote tweets, deduplicated views.
- **Profiles & Relations**: Profile viewing, tab filters (posts, replies, media, likes), follow/unfollow toggle, self-follow restriction, block/mute timeline exclusion, profile information and avatar/banner upload.
- **Timeline & Storage**: For you / Following feed filtering, pagination, public storage upload (images and videos), oversized media validation, physical storage file deletion.
- **Notifications**: Like, retweet, reply, and follow notification triggers, self-interaction filtering, unread badge counter and mark-as-read.
- **Search & Explore**: Keyword tweet search, people search, trending hashtags and explore page.

---

## 🛡️ Security Measures
- **Authorization Policies**: `TweetPolicy` prevents IDOR vulnerabilities (only tweet authors can delete their tweets).
- **FormRequest Validations**: Strict MIME type checking, size limitations (5MB for images, 50MB for videos), and character length guards.
- **Route Injection Shield**: Single-segment profile routing (`/{username}`) is protected with negative lookahead regex to prevent shadowing core routes.
- **XSS & CSRF**: All Inertia/Vue templates escape output by default; Laravel CSRF protection enabled on all mutation endpoints.
