-- SQL para agregar campos area y group si no existen
ALTER TABLE people ADD COLUMN area VARCHAR(100);
ALTER TABLE people ADD COLUMN group VARCHAR(100);