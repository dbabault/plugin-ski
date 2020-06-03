-

ALTER TABLE galette_lend_rents DROP FOREIGN KEY FK_rent_object_1;
ALTER TABLE galette_lend_rents ADD CONSTRAINT FK_rent_object_1 FOREIGN KEY (rent_id) REFERENCES galette_lend_objects (rent_id) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE galette_lend_objects DROP FOREIGN KEY FK_object_rent_1;
ALTER TABLE galette_lend_objects ADD CONSTRAINT FK_object_rent_1 FOREIGN KEY (rent_id) REFERENCES galette_lend_rents (rent_id) ON DELETE CASCADE ON UPDATE CASCADE;

