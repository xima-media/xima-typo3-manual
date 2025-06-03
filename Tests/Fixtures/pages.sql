INSERT INTO `pages` (`uid`, `pid`, `sorting`, `title`, `doktype`, `is_siteroot`, `slug`, `tsconfig_includes`, `backend_layout`)
VALUES
	(1,0,256,'Home',1,1,'/', NULL,''),
	(2,1,256,'My first page',1,0,'/my-first-page', NULL,''),
	(3,0,512,'Manual',701,1,'/', 'EXT:xima_typo3_manual/Configuration/TSconfig/Page.tsconfig','pagets__manualHomepage'),
	(4,3,256,'First Chapter',701,0,'/first-manual-page', NULL,''),
	(5,3,512,'Second Chapter',701,0,'/second-chapter', NULL,''),
	(6,5,256,'Subchapter',701,0,'/second-chapter/subchapter', NULL,'');

