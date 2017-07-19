CREATE TABLE IF NOT EXISTS {$DBPREFIX$}{$PACKAGENAME$}_{$MODULENAME$}_profiles (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID#',
  iid VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'Internal ID#',
  urn VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'Profile URN',
  url VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'Profile URL',
  name VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'Name',
  avatar VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'Avatar URL',
  access_token VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'Access token',
  token_secret VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'Token secret',
  expiration_date DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT 'Expiration date/time',

  PRIMARY KEY (id)
) COMMENT 'Social media profiles';


CREATE TABLE IF NOT EXISTS {$DBPREFIX$}{$PACKAGENAME$}_{$MODULENAME$}_groups (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID#',
  iid VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'Internal ID#',
  urn VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'Group URN',
  url VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'Group URL',
  name VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'Name',
  avatar VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'Avatar URL',
  group_type VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'Group Type',
  access_token VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'Page access token',

  PRIMARY KEY (id)
) COMMENT 'Social media profiles';


CREATE TABLE IF NOT EXISTS {$DBPREFIX$}{$PACKAGENAME$}_{$MODULENAME$}_tasks (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID#',
  material_type_id INT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Material type ID#',
  group_id INT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Group ID#',
  profile_id INT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Profile ID#',
  post_as_profile TINYINT(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Publish as profile',
  description TEXT NULL DEFAULT NULL COMMENT 'Post template',
  interface_id INT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Post interface ID#',
  check_for_update TINYINT(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Check for updates',
  date_from DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT 'Publish materials from',
  
  PRIMARY KEY (id),
  KEY (material_type_id),
  KEY (group_id),
  KEY (profile_id),
  KEY (interface_id)
) COMMENT 'Social media material types sync tasks';


CREATE TABLE IF NOT EXISTS {$DBPREFIX$}{$PACKAGENAME$}_{$MODULENAME$}_tasks_images (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID#',
  pid INT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Task ID#',
  fid INT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Field ID#',
  max_count INT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Max files to upload (0 - infinite)',
  priority INT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Priority',

  PRIMARY KEY (id),
  KEY (pid),
  KEY (fid),
  INDEX (priority)
) COMMENT 'Tasks images fields';


CREATE TABLE IF NOT EXISTS {$DBPREFIX$}{$PACKAGENAME$}_{$MODULENAME$}_tasks_documents (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID#',
  pid INT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Task ID#',
  fid INT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Field ID#',
  max_count INT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Max files to upload (0 - infinite)',
  priority INT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Priority',

  PRIMARY KEY (id),
  KEY (pid),
  KEY (fid),
  INDEX (priority)
) COMMENT 'Tasks documents fields';


CREATE TABLE IF NOT EXISTS {$DBPREFIX$}{$PACKAGENAME$}_{$MODULENAME$}_uploads (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID#',
  iid VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'Internal ID#',
  url VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'Upload URL',
  task_id INT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Task ID#',
  attachment_id INT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Attachment ID#',
  upload_type ENUM('document', 'image') NULL DEFAULT NULL COMMENT 'Upload type',
  post_id INT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Post ID#',
  material_id INT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Material ID#',
  group_id INT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Group ID#',
  profile_id INT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Profile ID#',
  post_date DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT 'Post date',
  
  PRIMARY KEY (id),
  KEY (task_id),
  KEY (attachment_id),
  KEY (post_id),
  KEY (material_id),
  KEY (group_id),
  KEY (profile_id)
) COMMENT 'Social media attachments upload log';


CREATE TABLE IF NOT EXISTS {$DBPREFIX$}{$PACKAGENAME$}_{$MODULENAME$}_posts (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID#',
  iid VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'Internal ID#',
  url VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'Upload URL',
  task_id INT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Task ID#',
  material_id INT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Material ID#',
  group_id INT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Group ID#',
  profile_id INT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Profile ID#',
  post_date DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT 'Post date',
  name VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'Name',
  
  PRIMARY KEY (id),
  KEY (task_id),
  KEY (material_id),
  KEY (group_id),
  KEY (profile_id)
) COMMENT 'Social media materials post log';