create table pages
(
	tx_ximatypo3manual_relations text,
);

create table tt_content
(
	tx_ximatypo3manual_relations text,
);

create table tx_ximatypo3manual_domain_model_term
(
	title       varchar(255) DEFAULT '' NOT NULL,
	description text,
	synonyms    text,
	link        varchar(255) DEFAULT '' NOT NULL,
);
