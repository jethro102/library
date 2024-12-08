# 📚 **Library Management API**

The **Library Management API** is an advanced backend service designed to streamline library operations, ensuring a smooth user experience for librarians and book enthusiasts. This API enables **secure access**, **role-based functionality**, and **intelligent resource management** for users, books, authors, and their relationships.

---

## ✨ **Key Features**

### 1. **Smart User Management**
   - **User Lifecycle**: End-to-end support for user registration, authentication, and account management.
   - **Granular Roles**: Roles include Admin, Librarian, and Member, each with tailored permissions.
   - **Account Recovery**: Password reset with time-bound OTPs for enhanced security.

### 2. **Dynamic Library Book Operations**
   - **Intuitive CRUD Operations**: Create, retrieve, update, and delete book records efficiently.
   - **Search & Filter**: Advanced filtering and searching by title, author, or genre.
   - **Batch Operations**: Add or update multiple books in one API call for bulk management.

### 3. **Sophisticated Author Management**
   - **Author Analytics**: View authors' publication count and their most popular books.
   - **Profile Images**: Add or update author profile pictures for a personalized touch.
   - **Collaboration Insights**: Track co-authoring projects between authors.

### 4. **Enhanced Book-Author Relationships**
   - **Bi-Directional Linking**: Automatically sync relationships between books and their authors.
   - **Conflict Detection**: Prevent duplicate relationships or invalid links.
   - **Audit Logs**: Detailed logs of changes to book-author mappings for traceability.

### 5. **Comprehensive Security Architecture**
   - **JWT Authentication**: Secures all API endpoints with tamper-proof JSON Web Tokens.
   - **One-Time Use Tokens**: Time-sensitive tokens for critical operations to prevent replay attacks.
   - **IP Rate Limiting**: Protects against brute force attacks by restricting excessive API requests.

---

## 🔒 **Security Features**

1. **JWT Token Lifecycle**
   - Tokens expire after a configurable duration, requiring users to re-authenticate periodically.
   - Refresh tokens available for uninterrupted user sessions.

2. **Sensitive Action Validation**
   - Actions like deleting user accounts or bulk book deletions require multi-factor verification.
   - OTPs are delivered via email or SMS for maximum reach.

3. **Comprehensive Input Sanitization**
   - Validates and sanitizes all inputs to eliminate SQL injection, XSS, and other attack vectors.
   - Enforces mandatory fields, unique constraints, and proper data formats.

---

## 🛠️ **Tech Stack**

Ensure the following prerequisites are installed:

- **PHP 8.0+** (with Slim Framework 4)
- **MySQL 8.0+**
- **Composer** (Dependency Manager)
- **Node.js 16+**
- **JWT PHP Library**
- **Docker** (for containerized environments)
- **Postman** or **Thunder Client** (for API testing)

---

## 🗂️ **API Endpoint Overview**

### **User Endpoints**

| Method | Endpoint                         | Description                          |
|--------|----------------------------------|--------------------------------------|
| POST   | `/user/register`                 | Register a new user.                 |
| POST   | `/user/login`                    | Log in and receive a JWT token.      |
| POST   | `/user/reset-password`           | Initiate password reset.             |
| PUT    | `/user/update-profile`           | Update user details.                 |
| DELETE | `/user/delete-account`           | Delete user account (Admin only).    |

---

### **Book Endpoints**

| Method | Endpoint                         | Description                          |
|--------|----------------------------------|--------------------------------------|
| POST   | `/books`                         | Add a new book.                      |
| GET    | `/books`                         | Retrieve all books with filters.     |
| GET    | `/books/{id}`                    | Retrieve specific book details.      |
| PUT    | `/books/{id}`                    | Update book details.                 |
| DELETE | `/books/{id}`                    | Delete a book.                       |
| POST   | `/books/bulk`                    | Add multiple books at once.          |

---

### **Author Endpoints**

| Method | Endpoint                         | Description                          |
|--------|----------------------------------|--------------------------------------|
| POST   | `/authors`                       | Add a new author.                    |
| GET    | `/authors`                       | Retrieve all authors.                |
| GET    | `/authors/{id}`                  | Retrieve specific author details.    |
| PUT    | `/authors/{id}`                  | Update author profile.               |
| DELETE | `/authors/{id}`                  | Delete an author.                    |
| GET    | `/authors/popular`               | Retrieve the most popular authors.   |

---

### **Book-Author Relationship Endpoints**

| Method | Endpoint                         | Description                          |
|--------|----------------------------------|--------------------------------------|
| POST   | `/relationships`                 | Add a book-author relationship.      |
| DELETE | `/relationships/{id}`            | Remove a book-author relationship.   |
| GET    | `/relationships`                 | View all relationships.              |

---

## 🚀 **Sample API Usage**

### **Register a User**
- **Endpoint**: `/user/register`
- **Request Body**:
  ```json
  {
    "username": "John David Padua",
    "password": "securepassword123",
    "email": "johndavid@example.com"
  }
  ```
- **Response**:
  ```json
  {
    "status": "success",
    "message": "User registered successfully!"
  }
  ```

---

### **Add a Book**
- **Endpoint**: `/books`
- **Headers**: `Authorization: Bearer <JWT>`
- **Request Body**:
  ```json
  {
    "title": "System Integration II",
    "author_ids": [12, 15],
    "genre": "Technology",
    "publication_year": 2023
  }
  ```
- **Response**:
  ```json
  {
    "status": "success",
    "message": "Book added successfully!"
  }
  ```

---

### **Fetch Popular Authors**
- **Endpoint**: `/authors/popular`
- **Headers**: `Authorization: Bearer <JWT>`
- **Response**:
  ```json
  {
    "status": "success",
    "data": [
      {
        "author_id": 12,
        "name": "John David Padua",
        "popular_books": ["System Integration II"]
      }
    ]
  }
  ```

---

## 📊 **Future Enhancements**

- **AI-Powered Recommendations**: Suggest books and authors to users based on borrowing patterns.
- **Real-Time Notifications**: Notify users about overdue books and reservation availability.
- **Integration with Third-Party Services**: Support for Google Books API and Goodreads sync.
