import requests
import json
import sqlite3

# Connect to the SQLite database
conn = sqlite3.connect('posts.db')
cursor = conn.cursor()

# Create a table to store the post data
cursor.execute('''CREATE TABLE IF NOT EXISTS posts
                  (id INT PRIMARY KEY,
                   status_code INT,
                   response_body TEXT,
                   post_body TEXT)''')

# Iterate through posts 1 to 10
for post_id in range(1, 11):
    api_url = f'https://jsonplaceholder.typicode.com/posts/{post_id}'  # API URL for the current post ID
    response = requests.get(api_url)  # Send GET request to the API

    # Extract relevant data from the response
    status_code = response.status_code
    response_body = json.dumps(response.json())
    data = response.json()
    post_body = data["body"]

    # Insert the data into the database
    cursor.execute("INSERT INTO posts (id, status_code, response_body, post_body) VALUES (?, ?, ?, ?)",
                   (post_id, status_code, response_body, post_body))

# Commit the changes and close the database connection
conn.commit()
conn.close()
