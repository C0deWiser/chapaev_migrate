INSERT INTO `nn6g0_workflow_associations` (`item_id`, `stage_id`, `extension`)
SELECT
    c.id AS item_id,
    1 AS stage_id,  -- ID этапа "Опубликовано"
    'com_content.article' AS extension
FROM nn6g0_content AS c
         LEFT JOIN nn6g0_workflow_associations AS wa
                   ON wa.item_id = c.id
                       AND wa.extension = 'com_content.article'
WHERE wa.item_id IS NULL;

INSERT INTO `nn6g0_ucm_base` (`ucm_id`, `ucm_item_id`, `ucm_type_id`, `ucm_language_id`)
SELECT
    uc.`core_content_id` AS ucm_id,
    uc.`core_content_item_id` AS ucm_item_id,
    1 AS ucm_type_id,
    0 AS ucm_language_id
FROM `nn6g0_ucm_content` AS uc
WHERE NOT EXISTS (
    SELECT 1
    FROM `nn6g0_ucm_base` AS ub
    WHERE ub.`ucm_id` = uc.`core_content_id`
)
ORDER BY uc.`core_content_id`;