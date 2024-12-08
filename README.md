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
| POST   | `/user`                          | Register a new user.                 |
| POST   | `/login`                         | Log in and receive a JWT token.      |
| POST   | `/userupdate`                    | Initiate user update profile.        |
| DELETE | `/userdelete`                    | initiate user deletion.              |
| GET    | `/displayalluser`                | Display all User Account             |

---

### **Book-Author Relationship Endpoints**

| Method | Endpoint                         | Description                          |
|--------|----------------------------------|--------------------------------------|
| POST   | `/add`                           | Add a book-author relationship.      |
| DELETE | `/delete/{id}`                   | Remove a book-author relationship.   |
| GET    | `/get`                           | View all relationships.              |
| PUT    | `/update/{id}`                   | update book-author relationship.     |

---

## 🚀 **Sample API Usage**

### **Register a User**
- **Endpoint**: `http://127.0.0.1/newlibrary/public/user`
- **Request Body**:
  ```json
  {
  "username":"dianee",
  "password":"novencido123"
  
  }
  ```
- **Response**:
  ```json
  {
  "status": "registration success, proceed to login"
  }
  ```


---

### **user login**
- **Endpoint**: `http://127.0.0.1/newlibrary/public/login`
- **Request Body**:
  ```json
  {
    "username":"dianee",
  "password":"novencido123"
  
  }
  ```
- **Response**:
  ```json
  {
      "status": "Login Success",
  "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vbGlicmFyeS5vcmciLCJhdWQiOiJodHRwOi8vbGlicmFyeS5jb20iLCJpYXQiOjE3MzM2NTgwNjgsImRhdGEiOnsidXNlcmlkIjoxMH19.14MlphuCAqiMuSGXo5qY1rWmRceYE0TAiUVf6GomhX8"
  }
  ```

---

### **display all user**
- **Endpoint**: `http://127.0.0.1/newlibrary/public/displayalluser`
- **Headers**: `Authorization: Bearer <JWT>`
- **Request Body**:
  ```json
  {
  
  }
  ```
- **Response**:
  ```json
  {
     "status": "success",
  "data": [
    {
      "userid": 1,
      "username": "john123"
    },
    {
      "userid": 2,
      "username": "jaliyah"
    },
    {
      "userid": 6,
      "username": "John David"
    },
    {
      "userid": 7,
      "username": "bea"
    },
    {
      "userid": 8,
      "username": "diane"
    },
    {
      "userid": 10,
      "username": "dianee"
    }
  ]
  }
  ```
---
### **user update**
- **Endpoint**: `http://127.0.0.1/newlibrary/public/userupdate/6`
- **Request Body**:
  ```json
  {

  "username":"Princess diane",
  "password":"novencidoo"

  }
  ```
- **Response**:
  ```json
  {
      "status": "success",
  "message": "User updated successfully"
  }
  ```
---
### **user delete**
- **Endpoint**: `http://127.0.0.1/newlibrary/public/userdelete/7`
- **Request Body**:
  ```json
  {

  }
  ```
- **Response**:
  ```json
  {
    "status": "success",
  "message": "User deleted successfully."
  }
  ```
---

### **add books and authors**
- **Endpoint**: `http://127.0.0.1/newlibrary/public/add`
- **Request Body**:
  ```json
  {
    "book":"diane literature 3",
  "author":"princess diane"
  }
  ```
- **Response**:
  ```json
  {
    "status": "Book added",
  "Here's your token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vbGlicmFyeS5vcmciLCJhdWQiOiJodHRwOi8vbGlicmFyeS5jb20iLCJpYXQiOjE3MzM2NTg3NTcsImRhdGEiOnsidXNlcmlkIjoxMH19.RLODmonzplIByILCrRqIKYBMctM8c6OmfCE68XHsB14"
  }
  ```
---
### **delete books and authors**
- **Endpoint**: `http://127.0.0.1/newlibrary/public/delete/5`
- **Request Body**:
  ```json
  {
  
  }
  ```
- **Response**:
  ```json
  {
    "status": "Delete success",
  "Here's your token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vbGlicmFyeS5vcmciLCJhdWQiOiJodHRwOi8vbGlicmFyeS5jb20iLCJpYXQiOjE3MzM2NTg4OTIsImRhdGEiOnsidXNlcmlkIjoxMH19.WZS7y75vzvyV9nAA-0fM8WUn9qAN4zBwoLd9mgbohMo"
  }
  ```
---
### **display all books and authors**
- **Endpoint**: `http://127.0.0.1/newlibrary/public/booksauthors`
- **Request Body**:
  ```json
  {
  
  }
  ```
- **Response**:
  ```json
  {
  
  "status": "success",
  "data": [
    {
      "book_id": 2,
      "book_title": "Dance Choreography",
      "author_name": "Jaliyah Iyane"
    },
    {
      "book_id": 3,
      "book_title": "david book",
      "author_name": "john david padua"
    },
    {
      "book_id": 4,
      "book_title": "diane literature",
      "author_name": "princess diane"
    },
    {
      "book_id": 6,
      "book_title": "diane literature 2",
      "author_name": "princess diane"
    },
    {
      "book_id": 7,
      "book_title": "diane literature 3",
      "author_name": "princess diane"
    }
  ]

  }
  ```
---
## 📊 **Future Enhancements**

- **AI-Powered Recommendations**: Suggest books and authors to users based on borrowing patterns.
- **Real-Time Notifications**: Notify users about overdue books and reservation availability.
- **Integration with Third-Party Services**: Support for Google Books API and Goodreads sync.
