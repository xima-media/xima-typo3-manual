create table pages (
	tx_ximatypo3manual_relations text,
);

create table tt_content (
	tx_ximatypo3manual_relations text,
	tx_ximatypo3manual_parent int(11) unsigned default '0' not null,
	tx_ximatypo3manual_children int(11) unsigned default '0' not null,
);
