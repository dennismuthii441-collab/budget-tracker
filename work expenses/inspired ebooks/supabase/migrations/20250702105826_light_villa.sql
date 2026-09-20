-- Create database
CREATE DATABASE IF NOT EXISTS ebook_site;
USE ebook_site;

-- Users table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    is_admin BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Books table
CREATE TABLE books (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    author VARCHAR(100) NOT NULL,
    description TEXT,
    cover_image VARCHAR(255),
    price DECIMAL(10,2) NOT NULL,
    preview_chapters INT DEFAULT 2,
    total_chapters INT NOT NULL,
    file_path VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Purchases table
CREATE TABLE purchases (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    book_id INT,
    payment_method ENUM('mpesa', 'paypal') NOT NULL,
    transaction_id VARCHAR(100),
    amount DECIMAL(10,2) NOT NULL,
    status ENUM('pending', 'completed', 'failed') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (book_id) REFERENCES books(id) ON DELETE CASCADE
);

-- Book chapters table
CREATE TABLE book_chapters (
    id INT AUTO_INCREMENT PRIMARY KEY,
    book_id INT,
    chapter_number INT NOT NULL,
    chapter_title VARCHAR(200),
    content TEXT NOT NULL,
    is_preview BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (book_id) REFERENCES books(id) ON DELETE CASCADE
);

-- Insert admin user (password: admin123)
INSERT INTO users (username, email, password, full_name, is_admin) 
VALUES ('admin', 'admin@ebooks.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator', TRUE);

-- Insert sample books
INSERT INTO books (title, author, description, price, preview_chapters, total_chapters) VALUES
('The Great Adventure', 'John Smith', 'An exciting tale of adventure and discovery in unknown lands. Follow our hero as he embarks on a journey that will change his life forever.', 9.99, 2, 15),
('Mystery of the Lost City', 'Jane Doe', 'A thrilling mystery novel about an ancient city lost in time. Archaeologists discover secrets that were meant to stay buried.', 12.99, 3, 20),
('Digital Future', 'Tech Writer', 'Explore the possibilities of our digital future and how technology will shape our lives in the coming decades.', 15.99, 2, 12);

-- Insert sample chapters for first book
INSERT INTO book_chapters (book_id, chapter_number, chapter_title, content, is_preview) VALUES
(1, 1, 'The Beginning', 'It was a dark and stormy night when our adventure began. The wind howled through the trees as Marcus packed his belongings, knowing that tomorrow would change everything. He had received the mysterious letter just three days ago, and despite his better judgment, he knew he had to follow its instructions.\n\nThe letter spoke of an ancient treasure hidden in the mountains, guarded by secrets that had been kept for centuries. Marcus had always been drawn to adventure, but this felt different. This felt real.\n\nAs he closed his suitcase, he wondered if he would ever see his comfortable home again. The journey ahead was uncertain, dangerous even, but the call of adventure was stronger than his fear.', TRUE),
(1, 2, 'The Journey Starts', 'The morning sun cast long shadows as Marcus began his journey. The path ahead was treacherous, winding through dense forests and rocky terrain. He had studied the map countless times, memorizing every detail, every landmark that would guide him to his destination.\n\nHours passed as he walked, his backpack growing heavier with each step. But his determination never wavered. The stories his grandfather had told him as a child came flooding back - tales of brave explorers who ventured into the unknown and returned with incredible stories.\n\nAs the sun began to set, Marcus realized he was no longer following a familiar path. The adventure had truly begun.', TRUE),
(1, 3, 'The First Challenge', 'Deep in the forest, Marcus encountered his first real challenge. A raging river blocked his path, its waters swift and dangerous. The map showed a bridge, but years of storms had washed it away, leaving only broken wooden posts as evidence of its existence.\n\nMarcus studied the situation carefully. He could try to swim across, but the current was too strong. He could search for another crossing, but that would add days to his journey. Then he noticed something - a fallen tree stretched most of the way across the river.\n\nWith careful planning and a bit of courage, he might be able to use it as a bridge. The risk was enormous, but so was the reward that awaited him.', FALSE);

-- Insert sample chapters for second book
INSERT INTO book_chapters (book_id, chapter_number, chapter_title, content, is_preview) VALUES
(2, 1, 'The Discovery', 'Dr. Sarah Chen had spent her entire career searching for proof that the lost city of Zephyria actually existed. Most of her colleagues dismissed it as myth, but the ancient texts she had studied told a different story.\n\nThe breakthrough came when her team discovered a series of stone tablets buried deep in the Amazon rainforest. The symbols matched those described in the oldest manuscripts, and they pointed to a location that had never been explored by modern archaeologists.\n\nAs Sarah examined the tablets under her magnifying glass, she felt a chill run down her spine. The symbols seemed to be warning of something - a danger that awaited those who disturbed the ancient city.', TRUE),
(2, 2, 'Into the Unknown', 'The expedition team consisted of six members: Sarah, two fellow archaeologists, a local guide, and two graduate students eager for adventure. They had prepared for months, gathering supplies and studying satellite images of the target area.\n\nThe jungle was unforgiving. Thick vines blocked their path, exotic birds called out warnings from the canopy above, and the humidity made every step feel like a marathon. But Sarah pressed on, driven by the possibility of making the discovery of a lifetime.\n\nOn the third day, their guide stopped suddenly and pointed ahead. Through the dense foliage, they could see the outline of stone structures covered in centuries of vegetation. They had found it - the lost city of Zephyria.', TRUE),
(2, 3, 'The Warning', 'As the team began to clear away the vegetation from the ancient structures, they uncovered more stone tablets. These were different from the ones they had found earlier - the symbols were more urgent, more desperate.\n\nSarah worked feverishly to translate the ancient text. What she discovered made her blood run cold. The tablets spoke of a curse, a protection placed on the city by its last inhabitants. Those who entered with greed in their hearts would face terrible consequences.\n\nBut Sarah was not driven by greed - she was driven by knowledge. Surely the ancient curse would not apply to someone seeking to preserve and understand the past. Or would it?', FALSE);

-- Insert sample chapters for third book
INSERT INTO book_chapters (book_id, chapter_number, chapter_title, content, is_preview) VALUES
(3, 1, 'The Digital Revolution', 'We stand at the threshold of a new era. The digital revolution that began decades ago is accelerating at an unprecedented pace, transforming every aspect of human life. From artificial intelligence to quantum computing, from virtual reality to biotechnology, the future is being written in code.\n\nThis transformation is not just about technology - it is about humanity itself. How we work, how we learn, how we connect with each other, and how we understand our place in the universe. The digital future is not something that will happen to us; it is something we are actively creating.\n\nIn this book, we will explore the possibilities and challenges that lie ahead, examining how emerging technologies will reshape our world and what it means to be human in an increasingly digital age.', TRUE),
(3, 2, 'Artificial Intelligence and Society', 'Artificial Intelligence is no longer the stuff of science fiction. It is here, now, integrated into our daily lives in ways we often do not even notice. From the algorithms that curate our social media feeds to the systems that power our smartphones, AI is becoming as fundamental to modern life as electricity.\n\nBut this is just the beginning. As AI systems become more sophisticated, they will take on increasingly complex tasks. They will diagnose diseases, drive our cars, manage our cities, and even create art and literature. The question is not whether AI will transform society, but how we will adapt to these changes.\n\nThe key to navigating this transformation lies in understanding both the potential and the limitations of artificial intelligence, and in ensuring that these powerful tools serve humanity rather than replace it.', TRUE),
(3, 3, 'The Future of Work', 'The nature of work is changing rapidly. Automation and artificial intelligence are eliminating some jobs while creating others. Remote work, accelerated by global events, has shown that many tasks can be performed from anywhere in the world.\n\nIn the digital future, success will depend not on what you know, but on how quickly you can learn and adapt. The most valuable skills will be creativity, critical thinking, emotional intelligence, and the ability to work alongside intelligent machines.\n\nEducation systems will need to evolve to prepare people for this new reality. Lifelong learning will become not just an advantage, but a necessity. The future belongs to those who can embrace change and continuously reinvent themselves.', FALSE);