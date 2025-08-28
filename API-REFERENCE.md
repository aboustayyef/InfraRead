# Infraread API Reference

## Overview

The Infraread API provides programmatic access to your RSS feed reader. All API endpoints are versioned and require authentication via Laravel Sanctum personal access tokens.

**Base URL:** `/api/v1`  
**Authentication:** Bearer token (Sanctum personal access tokens)  
**Content Type:** `application/json`

## Authentication

All API endpoints require authentication using a personal access token.

### Getting a Token
1. Log into the web interface
2. Go to Settings → API Tokens
3. Create a new token
4. Use the token in the `Authorization` header: `Bearer YOUR_TOKEN_HERE`

### Example Request
```bash
curl -H "Authorization: Bearer YOUR_TOKEN_HERE" \
     -H "Content-Type: application/json" \
     https://your-domain.com/api/v1/posts
```

---

## Posts API

### Get Posts
Retrieve a paginated list of posts with optional filtering.

**Endpoint:** `GET /api/v1/posts`

**Query Parameters:**
- `source_id` (integer, optional) - Filter by source
- `category_id` (integer, optional) - Filter by category  
- `include` (string, optional) - Include relationships: `source`, `category`, `source,category`
- `page` (integer, optional) - Page number for pagination

**Example Request:**
```bash
GET /api/v1/posts?include=source,category&source_id=1
```

**Example Response:**
```json
{
  "data": [
    {
      "id": 1,
      "title": "Sample Post Title",
      "content": "Post content here...",
      "url": "https://example.com/post",
      "read": false,
      "posted_at": "2025-08-14T10:00:00.000000Z",
      "source": {
        "id": 1,
        "name": "Tech Blog",
        "description": "Latest tech news"
      },
      "category": {
        "id": 1,
        "description": "Technology"
      }
    }
  ],
  "links": {
    "first": "http://localhost/api/v1/posts?page=1",
    "last": "http://localhost/api/v1/posts?page=5",
    "prev": null,
    "next": "http://localhost/api/v1/posts?page=2"
  },
  "meta": {
    "current_page": 1,
    "per_page": 15,
    "total": 67
  }
}
```

### Get Single Post
**Endpoint:** `GET /api/v1/posts/{id}`

### Mark Post Read/Unread
**Endpoint:** `PATCH /api/v1/posts/{id}/read-status`

**Request Body:**
```json
{
  "read": true
}
```

**Example Response:**
```json
{
  "message": "Post marked as read",
  "data": {
    "id": 1,
    "read": true
  }
}
```

### Bulk Mark Posts Read/Unread
Mark multiple posts as read or unread (up to 1000 posts per request).

**Endpoint:** `PATCH /api/v1/posts/bulk-read-status`

**Request Body:**
```json
{
  "post_ids": [1, 2, 3, 4, 5],
  "read": true
}
```

**Example Response:**
```json
{
  "message": "5 posts marked as read",
  "data": {
    "updated_count": 5,
    "requested_count": 5
  }
}
```

### Mark All Posts Read/Unread
Efficiently mark all posts as read/unread with optional filtering.

**Endpoint:** `PATCH /api/v1/posts/mark-all-read`

**Request Body:**
```json
{
  "read": true,
  "source_id": 1,
  "category_id": 2,
  "before_date": "2025-08-14"
}
```

**Example Response:**
```json
{
  "message": "25 posts marked as read",
  "data": {
    "updated_count": 25
  }
}
```

### Generate Post Summary
Generate an AI summary for a post (rate limited).

**Endpoint:** `POST /api/v1/posts/{id}/summary`

**Example Response:**
```json
{
  "data": {
    "summary": "This article discusses the latest developments in AI technology..."
  }
}
```

---

## Sources API

### Get Sources
**Endpoint:** `GET /api/v1/sources`

**Query Parameters:**
- `include` (string, optional) - Include relationships: `category`

### Get Single Source
**Endpoint:** `GET /api/v1/sources/{id}`

### Create Source
Add a new RSS feed source with automatic discovery.

**Endpoint:** `POST /api/v1/sources`

**Request Body:**
```json
{
  "url": "https://techcrunch.com/feed/",
  "category_id": 1,
  "name": "TechCrunch",
  "description": "Technology news and analysis"
}
```

**Example Response:**
```json
{
  "message": "Source created successfully",
  "data": {
    "id": 5,
    "name": "TechCrunch", 
    "description": "Technology news and analysis",
    "url": "https://techcrunch.com",
    "fetcher_source": "https://techcrunch.com/feed/",
    "category_id": 1,
    "active": true,
    "metadata": {
      "discovered_feeds": ["https://techcrunch.com/feed/"],
      "site_title": "TechCrunch",
      "site_description": "Latest technology news"
    }
  }
}
```

### Update Source
**Endpoint:** `PUT /api/v1/sources/{id}`

### Delete Source
**Endpoint:** `DELETE /api/v1/sources/{id}`

### Refresh Source Posts
Force refresh posts from a source.

**Endpoint:** `POST /api/v1/sources/{id}/refresh`

**Example Response:**
```json
{
  "message": "Source refresh initiated",
  "data": {
    "source_id": 1,
    "status": "queued"
  }
}
```

---

## Categories API

### Get Categories
**Endpoint:** `GET /api/v1/categories`

**Example Response:**
```json
{
  "data": [
    {
      "id": 1,
      "description": "Technology",
      "sources_count": 5,
      "created_at": "2025-08-14T10:00:00.000000Z",
      "updated_at": "2025-08-14T10:00:00.000000Z"
    }
  ]
}
```

### Get Single Category
**Endpoint:** `GET /api/v1/categories/{id}`

### Create Category
**Endpoint:** `POST /api/v1/categories`

**Request Body:**
```json
{
  "description": "Science News"
}
```

### Update Category
**Endpoint:** `PUT /api/v1/categories/{id}`

### Delete Category
Deletes a category and moves all sources to "Uncategorized".

**Endpoint:** `DELETE /api/v1/categories/{id}`

**Example Response:**
```json
{
  "message": "Category deleted successfully",
  "data": {
    "sources_moved": 3,
    "moved_to_category": "Uncategorized"
  }
}
```

---

## OPML API

### Export OPML
Export all sources as OPML format for backup or migration.

**Endpoint:** `GET /api/v1/export-opml`

**Example Response:**
```json
{
  "message": "OPML exported successfully",
  "data": {
    "content": "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<opml version=\"2.0\">...",
    "filename": "infraread-feeds-2025-08-14.opml",
    "sources_count": 25,
    "categories_count": 5
  }
}
```

### Preview OPML Import
Preview what will be imported from an OPML file without executing the import.

**Endpoint:** `POST /api/v1/preview-opml`

**Request Body:** Multipart form data
- `opml` (file) - OPML file to preview

**Example Response:**
```json
{
  "message": "OPML preview generated successfully",
  "data": {
    "categories": [
      {
        "name": "Technology",
        "sources": [
          {
            "name": "TechCrunch",
            "url": "https://techcrunch.com/feed/",
            "site_url": "https://techcrunch.com"
          }
        ],
        "source_count": 1
      }
    ],
    "uncategorized_sources": [],
    "total_categories": 1,
    "total_sources": 1
  }
}
```

### Import OPML
Import sources from an OPML file.

**Endpoint:** `POST /api/v1/import-opml`

**Request Body:** Multipart form data
- `opml` (file) - OPML file to import
- `mode` (string, optional) - Import mode: `replace` (default) or `merge`

**Example Response:**
```json
{
  "message": "OPML imported successfully",
  "data": {
    "mode": "replace",
    "categories_created": 3,
    "sources_created": 15,
    "sources_skipped": 0,
    "errors": []
  }
}
```

---

## Error Responses

All endpoints return consistent error responses:

### Validation Errors (422)
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "url": ["The url field is required."],
    "category_id": ["The category id must be an integer."]
  }
}
```

### Authentication Required (401)
```json
{
  "message": "Unauthenticated."
}
```

### Not Found (404)
```json
{
  "message": "No query results for model [App\\Models\\Post] 999"
}
```

### Rate Limited (429)
```json
{
  "message": "Too Many Attempts."
}
```

### Server Error (500)
```json
{
  "message": "Server Error",
  "error": "An unexpected error occurred"
}
```

---

## Rate Limiting

- **Summary Generation:** 10 requests per minute per user
- **Source Management:** 60 requests per minute per user  
- **General API:** 100 requests per minute per user

---

## Pagination

All list endpoints use Laravel's standard pagination:

```json
{
  "data": [...],
  "links": {
    "first": "http://localhost/api/v1/posts?page=1",
    "last": "http://localhost/api/v1/posts?page=5", 
    "prev": null,
    "next": "http://localhost/api/v1/posts?page=2"
  },
  "meta": {
    "current_page": 1,
    "from": 1,
    "last_page": 5,
    "per_page": 15,
    "to": 15,
    "total": 67
  }
}
```

---

## Examples

### Complete Workflow Example

```bash
# 1. Get your API token from the web interface first

# 2. List all posts
curl -H "Authorization: Bearer YOUR_TOKEN" \
     https://your-domain.com/api/v1/posts

# 3. Mark specific posts as read
curl -X PATCH \
     -H "Authorization: Bearer YOUR_TOKEN" \
     -H "Content-Type: application/json" \
     -d '{"post_ids": [1,2,3], "read": true}' \
     https://your-domain.com/api/v1/posts/bulk-read-status

# 4. Add a new source
curl -X POST \
     -H "Authorization: Bearer YOUR_TOKEN" \
     -H "Content-Type: application/json" \
     -d '{"url": "https://example.com/feed.xml", "category_id": 1}' \
     https://your-domain.com/api/v1/sources

# 5. Export OPML backup
curl -H "Authorization: Bearer YOUR_TOKEN" \
     https://your-domain.com/api/v1/export-opml
```

---

## Jobs API

The Jobs API allows you to trigger background processing tasks and monitor their status. All jobs are processed asynchronously using Laravel's queue system.

### Refresh Source Job
Triggers a background job to manually refresh posts from a specific RSS source.

**Endpoint:** `POST /api/v1/jobs/sources/{source}/refresh`

**Parameters:**
- `source` (integer, required) - The source ID to refresh

**Example Request:**
```bash
POST /api/v1/jobs/sources/1/refresh
```

**Example Response:**
```json
{
  "message": "Source refresh job dispatched successfully",
  "data": {
    "source_id": 1,
    "source_name": "Tech News",
    "job_dispatched": true,
    "estimated_completion": "2025-08-16T10:30:00Z"
  }
}
```

### Generate Summary Job
Dispatches a job to generate an AI summary for a specific post.

**Endpoint:** `POST /api/v1/jobs/posts/{post}/summary`

**Parameters:**
- `post` (integer, required) - The post ID to summarize
- `sentences` (integer, optional) - Number of sentences for summary (default: 3, max: 10)

**Example Request:**
```bash
POST /api/v1/jobs/posts/123/summary
Content-Type: application/json

{
  "sentences": 5
}
```

**Example Response:**
```json
{
  "message": "Summary generation job dispatched successfully",
  "data": {
    "post_id": 123,
    "cache_key": "summary_post_123_5",
    "job_dispatched": true,
    "estimated_completion": "2025-08-16T10:32:00Z",
    "status_check_url": "/api/v1/jobs/summary-status/summary_post_123_5"
  }
}
```

### Summary Status
Check the status of a summary generation job.

**Endpoint:** `GET /api/v1/jobs/summary-status/{cacheKey}`

**Parameters:**
- `cacheKey` (string, required) - The cache key returned from generate summary job

**Example Request:**
```bash
GET /api/v1/jobs/summary-status/summary_post_123_5
```

**Example Response (Processing):**
```json
{
  "message": "Summary generation in progress",
  "data": {
    "status": "processing",
    "cache_key": "summary_post_123_5",
    "started_at": "2025-08-16T10:30:00Z"
  }
}
```

**Example Response (Completed):**
```json
{
  "message": "Summary generation completed",
  "data": {
    "status": "completed",
    "cache_key": "summary_post_123_5",
    "summary": "This is the generated AI summary of the post content...",
    "sentences": 5,
    "completed_at": "2025-08-16T10:31:30Z"
  }
}
```

### Queue Status
Get information about the job queue system status.

**Endpoint:** `GET /api/v1/jobs/queue-status`

**Example Request:**
```bash
GET /api/v1/jobs/queue-status
```

**Example Response:**
```json
{
  "message": "Queue status retrieved successfully",
  "data": {
    "queues": {
      "default": {
        "pending_jobs": 5,
        "failed_jobs": 0
      },
      "refresh": {
        "pending_jobs": 2,
        "failed_jobs": 1
      }
    },
    "total_pending": 7,
    "total_failed": 1,
    "system_healthy": true,
    "generated_at": "2025-08-16T10:30:00Z"
  }
}
```

---

## Metrics API

The Metrics API provides observability and monitoring capabilities for your RSS reader system. These endpoints help track performance, health, and system statistics.

### Source Metrics
Get detailed metrics for a specific RSS source.

**Endpoint:** `GET /api/v1/metrics/sources/{source}`

**Parameters:**
- `source` (integer, required) - The source ID to get metrics for

**Example Request:**
```bash
GET /api/v1/metrics/sources/1
```

**Example Response:**
```json
{
  "message": "Source metrics retrieved successfully",
  "data": {
    "source_id": 1,
    "source_name": "Tech News",
    "source_url": "https://technews.com/feed.xml",
    "metrics": {
      "last_fetched_at": "2025-08-16T09:15:00Z",
      "last_fetch_duration_ms": 1200,
      "consecutive_failures": 0,
      "status": "active",
      "status_description": "Working normally",
      "last_error_at": null,
      "last_error_message": null,
      "next_attempt_at": null,
      "should_skip_backoff": false,
      "posts_count": 150,
      "unread_posts_count": 12,
      "latest_post_date": "2025-08-16T08:30:00Z",
      "is_healthy": true,
      "is_failed": false
    }
  }
}
```

### System Statistics
Get comprehensive system-wide statistics and performance metrics.

**Endpoint:** `GET /api/v1/metrics/system`

**Example Request:**
```bash
GET /api/v1/metrics/system
```

**Example Response:**
```json
{
  "message": "System processing statistics retrieved successfully",
  "data": {
    "sources": {
      "total_sources": 15,
      "active_sources": 14,
      "healthy_sources": 12,
      "warning_sources": 2,
      "failed_sources": 0
    },
    "posts": {
      "total_posts": 2540,
      "unread_posts": 45,
      "posts_today": 8,
      "posts_this_week": 52,
      "posts_this_month": 234
    },
    "categories": {
      "total_categories": 5,
      "categories_with_sources": 5
    },
    "performance": {
      "sources_updated_today": 12,
      "average_fetch_duration_ms": 1150,
      "fastest_source_ms": 340,
      "slowest_source_ms": 3200
    },
    "errors": {
      "sources_with_errors": 2,
      "total_consecutive_failures": 3,
      "sources_in_backoff": 1
    },
    "generated_at": "2025-08-16T10:30:00Z",
    "cache_duration": "5 minutes"
  }
}
```

### Sources Health Summary
Get a health overview of all RSS sources, highlighting problematic ones.

**Endpoint:** `GET /api/v1/metrics/sources-health`

**Example Request:**
```bash
GET /api/v1/metrics/sources-health
```

**Example Response:**
```json
{
  "message": "Sources health summary retrieved successfully",
  "data": {
    "summary": {
      "total": 15,
      "active": 14,
      "inactive": 1,
      "healthy": 12,
      "warning": 2,
      "failed": 0
    },
    "problematic_sources": [
      {
        "id": 7,
        "name": "Slow Feed",
        "status": "warning",
        "consecutive_failures": 2,
        "last_error_at": "2025-08-16T09:00:00Z",
        "last_error_message": "Connection timeout",
        "status_description": "Issues detected (2 recent failures)"
      }
    ],
    "generated_at": "2025-08-16T10:30:00Z"
  }
}
```

### Recent Activity
Get information about recent RSS processing activity.

**Endpoint:** `GET /api/v1/metrics/recent-activity`

**Example Request:**
```bash
GET /api/v1/metrics/recent-activity
```

**Example Response:**
```json
{
  "message": "Recent processing activity retrieved successfully",
  "data": {
    "recent_posts_count": 28,
    "recently_updated_sources": [
      {
        "source_id": 1,
        "source_name": "Tech News",
        "last_fetched_at": "2025-08-16T10:15:00Z",
        "duration_ms": 890,
        "status": "active",
        "consecutive_failures": 0
      }
    ],
    "time_range": "Last 24 hours",
    "generated_at": "2025-08-16T10:30:00Z"
  }
}
```

### Crawl Status
Get information about the last successful crawl and system health for display on the frontend.

**Endpoint:** `GET /api/v1/metrics/crawl-status`

**Example Request:**
```bash
GET /api/v1/metrics/crawl-status
```

**Example Response:**
```json
{
  "message": "Crawl status retrieved successfully",
  "data": {
    "last_successful_crawl": "2025-08-23T09:15:30Z",
    "minutes_since_last_crawl": 45,
    "is_recent": true,
    "warning_threshold_minutes": 120,
    "should_show_warning": false,
    "human_readable": "45 minutes ago",
    "status": "healthy"
  }
}
```

**Response Fields:**
- `last_successful_crawl` - ISO timestamp of the most recent successful crawl
- `minutes_since_last_crawl` - Minutes elapsed since last successful crawl
- `is_recent` - Boolean indicating if crawl is within acceptable timeframe
- `warning_threshold_minutes` - System threshold for showing warnings (default: 120 minutes)
- `should_show_warning` - Boolean indicating if frontend should display warning
- `human_readable` - User-friendly time description
- `status` - Overall status: "ok", "warning", "no_data", or "error"

**Example Response (No Data):**
```json
{
  "message": "Crawl status retrieved successfully",
  "data": {
    "status": "no_data",
    "message": "No information available about the last crawl. The system may not have run a full crawl cycle yet since switching to the new tracking system.",
    "last_crawl_at": null,
    "minutes_ago": null,
    "threshold_minutes": 80,
    "needs_attention": false,
    "human_readable": "No crawl data available"
  }
}
```

---

## Rate Limiting

All API endpoints are subject to rate limiting:
- **Default**: 60 requests per minute per user
- **Jobs API**: 30 requests per minute per user (due to resource-intensive operations)
- **Metrics API**: 120 requests per minute per user (read-only operations)

Rate limit headers are included in all responses:
- `X-RateLimit-Limit`: Total requests allowed
- `X-RateLimit-Remaining`: Remaining requests
- `X-RateLimit-Reset`: Unix timestamp when the rate limit resets

---

## Error Handling

### HTTP Status Codes
- `200` - Success
- `201` - Created (for POST operations)
- `202` - Accepted (for asynchronous job dispatch)
- `400` - Bad Request (validation errors)
- `401` - Unauthorized (missing or invalid token)
- `403` - Forbidden (insufficient permissions)
- `404` - Not Found
- `422` - Unprocessable Entity (validation failed)
- `429` - Too Many Requests (rate limited)
- `500` - Internal Server Error

### Error Response Format
```json
{
  "message": "Error description",
  "errors": {
    "field_name": ["Specific validation error"]
  }
}
```

---

## Version History

- **v1.0** - Initial API release with read operations and post management
- **v1.1** - Added source and category management
- **v1.2** - Added OPML import/export functionality
- **v1.3** - Added Jobs API for background processing and Metrics API for observability
- **v1.4** - Added crawl status endpoint for frontend health monitoring

---

Last Updated: August 16, 2025
