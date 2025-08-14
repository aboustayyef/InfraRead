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

## Version History

- **v1.0** - Initial API release with read operations and post management
- **v1.1** - Added source and category management
- **v1.2** - Added OPML import/export functionality

---

Last Updated: August 14, 2025
