-- ============================================================
--  Gasan News: A Web-Based Local Journalism Platform
--  Database Setup Script
-- ============================================================

CREATE DATABASE IF NOT EXISTS balitang_gasan_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE balitang_gasan_db;

-- ---------------------------------------------------------------
-- CATEGORIES
-- ---------------------------------------------------------------
CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ---------------------------------------------------------------
-- USERS  (admin / editor accounts)
-- ---------------------------------------------------------------
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    email VARCHAR(200) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin','editor') DEFAULT 'editor',
    avatar VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ---------------------------------------------------------------
-- ARTICLES
-- ---------------------------------------------------------------
CREATE TABLE articles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(300) NOT NULL,
    slug VARCHAR(300) NOT NULL UNIQUE,
    byline VARCHAR(200),
    category_id INT NOT NULL,
    author_id INT NOT NULL,
    body LONGTEXT NOT NULL,
    excerpt TEXT,
    image VARCHAR(255) DEFAULT NULL,
    status ENUM('published','draft') DEFAULT 'draft',
    views INT DEFAULT 0,
    published_at DATETIME DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE,
    FOREIGN KEY (author_id) REFERENCES users(id) ON DELETE CASCADE
);

-- ---------------------------------------------------------------
-- COMMENTS
-- ---------------------------------------------------------------
CREATE TABLE comments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    article_id INT NOT NULL,
    commenter_name VARCHAR(150) NOT NULL,
    commenter_email VARCHAR(200) NOT NULL,
    body TEXT NOT NULL,
    status ENUM('pending','approved','rejected') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (article_id) REFERENCES articles(id) ON DELETE CASCADE
);

-- ---------------------------------------------------------------
-- NEWSLETTER SUBSCRIBERS
-- ---------------------------------------------------------------
CREATE TABLE subscribers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(200) NOT NULL UNIQUE,
    subscribed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ---------------------------------------------------------------
-- SEED DATA
-- ---------------------------------------------------------------

-- Categories
INSERT INTO categories (name, slug, description) VALUES
('News',          'news',          'General local news for Gasan'),
('Events',        'events',        'Community events and gatherings'),
('Sports',        'sports',        'Local sports updates'),
('Business',      'business',      'Business and economy'),
('Politics',      'politics',      'Municipal governance and politics'),
('Travel',        'travel',        'Local destinations and the tourism industry'),
('Health',        'health',        'Health and wellness'),
('Education',     'education',     'Schools and academic activities');

-- Admin user  (password: password)
INSERT INTO users (name, email, password, role) VALUES
('Site Administrator', 'kenzander@balitanggaseno.news.ph',
 '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin'), 
 ('Field Reporter', 'danica@balitanggaseno.news.ph',
'$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin'), 
('Content Writer', 'jessica@balitanggaseno.news.ph',
'$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin'),
('Multimedia Specialist', 'j-ber@balitanggaseno.news.ph',
'$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- Note: the hashed password above corresponds to "password"
-- Change it after first login.

-- Sample articles
INSERT INTO articles (title, slug, byline, category_id, author_id, body, excerpt, status, published_at) VALUES
(
  'Gasan Celebrates its 398th Founding Anniversary with Grand Festivities',
  'gasan-398th-founding-anniversary',
  'By Maria Santos',
  1, 1,
  '<p>The municipality of Gasan marked another milestone as residents and local officials gathered in the town plaza to celebrate its 398th Founding Anniversary. The event was filled with cultural performances, traditional dances, and a grand parade showcasing the rich heritage of the Gasanons.</p><p>Mayor Juan dela Cruz led the opening ceremony, highlighting the town\'s progress and achievements over the years. Various community organizations participated in the festivities, making it a truly unified celebration.</p><p>The celebration also featured a street food festival where local delicacies were showcased, giving residents and visitors a taste of authentic Marinduque cuisine.</p>',
  'The municipality of Gasan marked another milestone as residents and local officials gathered to celebrate its 398th Founding Anniversary.',
  'published',
  NOW()
),
(
  'Local Fishermen Benefit from New Livelihood Program',
  'local-fishermen-livelihood-program',
  'By Jose Reyes',
  2, 1,
  '<p>A new livelihood program funded by the municipal government of Gasan is set to benefit over 200 local fishermen in the coastal barangays. The program includes the provision of modern fishing equipment, training on sustainable fishing practices, and access to micro-financing opportunities.</p><p>The initiative aims to boost the income of fishing families while promoting environmental sustainability in the waters surrounding Marinduque.</p>',
  'A new livelihood program is set to benefit over 200 local fishermen in the coastal barangays of Gasan.',
  'published',
  NOW()
),
(
  'Gasan Elementary School Tops Regional Science Fair',
  'gasan-elementary-tops-science-fair',
  'By Ana Dela Cruz',
  8, 1,
  '<p>Students from Gasan Central Elementary School have brought pride to the municipality after winning first place at the Regional Science Fair held in Boac. The winning project, titled "Sustainable Water Filtration Using Local Materials," impressed the judges with its practicality and environmental impact.</p><p>The student team, composed of Grade 6 pupils, was mentored by their science teacher, Mr. Roberto Lim, who expressed his pride in the students\' dedication and hard work.</p>',
  'Students from Gasan Central Elementary School brought pride to the municipality after winning first place at the Regional Science Fair.',
  'published',
  NOW()
),
(
  'Municipal Council Approves New Ordinance on Plastic Use',
  'municipal-council-plastic-ordinance',
  'By Carlos Mendoza',
  5, 1,
  '<p>The Gasan Municipal Council unanimously approved a new ordinance banning single-use plastics in all public markets and commercial establishments within the municipality. The ordinance, which takes effect next month, aims to reduce plastic waste in Gasan\'s waterways and promote a cleaner environment.</p><p>Violators will face fines ranging from PHP 500 to PHP 2,000 depending on the severity of the violation.</p>',
  'The Gasan Municipal Council unanimously approved a new ordinance banning single-use plastics in all commercial establishments.',
  'published',
  NOW()
),
(
  'Gasan Basketball League Season 3 Tips Off This Weekend',
  'gasan-basketball-league-season-3',
  'By Pedro Garcia',
  3, 1,
  '<p>The much-anticipated Season 3 of the Gasan Municipal Basketball League is set to tip off this Saturday at the Gasan Covered Court. A total of 12 barangay teams have registered, promising an exciting and competitive season for basketball fans in the municipality.</p><p>The opening ceremony will feature performances by local artists and the recognition of last season\'s championship team. Games will be held every Saturday and Sunday throughout the season.</p>',
  'The much-anticipated Season 3 of the Gasan Municipal Basketball League is set to tip off this Saturday at the Gasan Covered Court.',
  'published',
  NOW()
),
(
  'Health Center Opens New Maternal Care Wing',
  'health-center-maternal-care-wing',
  'By Dr. Liza Bautista',
  7, 1,
  '<p>The Gasan Rural Health Unit officially opened its new Maternal Care Wing last Monday, providing expanded services for pregnant women and new mothers in the municipality. The facility, funded through a grant from the provincial health office, features four delivery rooms, a breastfeeding lounge, and a newborn care station.</p><p>Municipal Health Officer Dr. Elena Santos said the new wing will significantly reduce referrals to Boac District Hospital for normal deliveries.</p>',
  'The Gasan Rural Health Unit officially opened its new Maternal Care Wing, providing expanded services for pregnant women.',
  'published',
  NOW()
);
