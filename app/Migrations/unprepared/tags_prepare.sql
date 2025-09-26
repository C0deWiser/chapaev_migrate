INSERT INTO `nn6g0_assets` (`parent_id`, `lft`, `rgt`, `level`, `name`, `title`, `rules`)
SELECT
    cat_asset.id AS parent_id,
    0 AS lft,
    0 AS rgt,
    (cat_asset.level + 1) AS level,  -- Сохраняем логику: родитель + 1
    CONCAT('com_content.article.', c.id) AS name,
    c.title AS title,
    '{}' AS rules
FROM nn6g0_content AS c
         INNER JOIN (
    SELECT id, level, CONCAT('com_content.category.', SUBSTRING_INDEX(name, '.', -1)) AS cat_name
    FROM nn6g0_assets
    WHERE name LIKE 'com_content.category.%'
) AS cat_asset ON cat_asset.cat_name = CONCAT('com_content.category.', c.catid)
         LEFT JOIN nn6g0_assets AS existing_asset
                   ON existing_asset.name = CONCAT('com_content.article.', c.id)
WHERE existing_asset.id IS NULL;


INSERT INTO `nn6g0_ucm_content` (
    `core_type_alias`,
    `core_title`,
    `core_alias`,
    `core_body`,
    `core_state`,
    `core_checked_out_time`,
    `core_checked_out_user_id`,
    `core_access`,
    `core_params`,
    `core_featured`,
    `core_metadata`,
    `core_created_user_id`,
    `core_created_by_alias`,
    `core_created_time`,
    `core_modified_user_id`,
    `core_modified_time`,
    `core_language`,
    `core_publish_up`,
    `core_publish_down`,
    `core_content_item_id`,
    `asset_id`,
    `core_images`,
    `core_urls`,
    `core_hits`,
    `core_version`,
    `core_ordering`,
    `core_metakey`,
    `core_metadesc`,
    `core_catid`,
    `core_type_id`
)
SELECT
    'com_content.article' AS `core_type_alias`,
    c.`title` AS `core_title`,
    c.`alias` AS `core_alias`,
    CONCAT(c.`introtext`, '\r\n', c.`fulltext`) AS `core_body`,
    c.`state` AS `core_state`,
    c.`checked_out_time` AS `core_checked_out_time`,
    c.`checked_out` AS `core_checked_out_user_id`,
    c.`access` AS `core_access`,
    c.`attribs` AS `core_params`,
    c.`featured` AS `core_featured`,
    c.`metadata` AS `core_metadata`,
    c.`created_by` AS `core_created_user_id`,
    c.`created_by_alias` AS `core_created_by_alias`,
    c.`created` AS `core_created_time`,
    COALESCE(c.`modified_by`, c.`created_by`) AS `core_modified_user_id`,
    c.`modified` AS `core_modified_time`,
    c.`language` AS `core_language`,
    c.`publish_up` AS `core_publish_up`,
    c.`publish_down` AS `core_publish_down`,
    c.`id` AS `core_content_item_id`,
    a.`id` AS `asset_id`,
    c.`images` AS `core_images`,
    c.`urls` AS `core_urls`,
    c.`hits` AS `core_hits`,
    c.`version` AS `core_version`,
    c.`ordering` AS `core_ordering`,
    c.`metakey` AS `core_metakey`,
    c.`metadesc` AS `core_metadesc`,
    c.`catid` AS `core_catid`,
    ct.`type_id` AS `core_type_id`
FROM `nn6g0_content` AS c
-- Получаем ассет для каждой статьи
         INNER JOIN `nn6g0_assets` AS a
                    ON a.`name` = CONCAT('com_content.article.', c.`id`)
-- Получаем type_id для com_content.article
         INNER JOIN `nn6g0_content_types` AS ct
                    ON ct.`type_alias` = 'com_content.article'
-- Исключаем уже существующие записи в ucm_content
         LEFT JOIN `nn6g0_ucm_content` AS u
                   ON u.`core_content_item_id` = c.`id`
                       AND u.`core_type_alias` = 'com_content.article'
WHERE u.`core_content_item_id` IS NULL;


UPDATE `nn6g0_content` AS c
    INNER JOIN `nn6g0_assets` AS a
    ON a.`name` = CONCAT('com_content.article.', c.`id`)
SET c.`asset_id` = a.`id`
WHERE c.`asset_id` = 0 OR c.`asset_id` IS NULL;