-- ----------------------------
-- Table structure for __PREFIX__bbs_forum
-- ----------------------------
CREATE TABLE IF NOT EXISTS `__PREFIX__bbs_forum`  (
                                 `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
                                 `name` char(16) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '板块名称',
                                 `weigh` tinyint(3) NOT NULL DEFAULT 0 COMMENT '排序(倒叙)',
                                 `thread_number` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '主题数',
                                 `today_posts` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '今日帖子数',
                                 `today_threads` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '今日主题数',
                                 `brief` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL COMMENT '简介',
                                 `announcement` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL COMMENT '公告',
                                 `createtime` int(10) NOT NULL DEFAULT 0 COMMENT '创建时间',
                                 `updatetime` int(10) NOT NULL DEFAULT 0 COMMENT '更新时间',
                                 `mod_user_ids` varchar(120) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '版主id',
                                 `post_number` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '帖子数量',
                                 `elite_number` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '加精数量',
                                 `status` tinyint(1) UNSIGNED NOT NULL DEFAULT 1 COMMENT '状态:0=禁用,1=启用',
                                 `thread_status` tinyint(1) DEFAULT 0 COMMENT '发帖限制:0=登录会员,1=仅版主,2=禁止发帖',
                                 PRIMARY KEY (`id`) USING BTREE,
                                 INDEX `weigh`(`weigh`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '板块表' ;

-- ----------------------------
-- Table structure for __PREFIX__bbs_thread
-- ----------------------------
CREATE TABLE IF NOT EXISTS `__PREFIX__bbs_thread`  (
    `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
    `forum_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '所属板块',
    `all_top` int(10) NOT NULL DEFAULT 0 COMMENT '全局置顶(倒叙最大是3)',
    `top` int(10) NOT NULL DEFAULT 0 COMMENT '板块置顶(倒叙最大是3)',
    `is_elite` tinyint(1) NOT NULL DEFAULT 0 COMMENT '是否是精华帖:1=是,0=否',
    `user_ip` char(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '创建时ip',
    `user_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '发帖用户',
    `title` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '主题(标题)',
    `brief` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '主题简介',
    `content_html` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL COMMENT '内容原文',
    `content_fmt` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL COMMENT '去除标签后的内容',
    `view_number` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '查看次数',
    `post_number` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '回帖数量',
    `collect_number` int(11) UNSIGNED NULL DEFAULT 0 COMMENT '收藏数量',
    `praise_number` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '点赞数量',
    `report_number` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '被举报次数',
    `last_user_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '最后发帖用户',
    `last_post_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '最后帖子',
    `last_time` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '最后回复时间',
    `status` tinyint(1) NOT NULL DEFAULT 0 COMMENT '状态:0=正常,1=关闭(只可查看)',
    `createtime` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '创建时间',
    `updatetime` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '更新时间',
    `deletetime` int(10) NULL DEFAULT NULL COMMENT '删除时间',
    PRIMARY KEY (`id`) USING BTREE,
    INDEX `last_post_id`(`last_post_id`) USING BTREE,
    INDEX `last_post_id_2`(`last_post_id`, `forum_id`) USING BTREE,
    INDEX `createtime`(`createtime`) USING BTREE,
    INDEX `updatetime`(`updatetime`) USING BTREE,
    INDEX `view_number`(`view_number`) USING BTREE,
    INDEX `post_number`(`post_number`) USING BTREE
    ) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '主题表' ;

-- ----------------------------
-- Table structure for __PREFIX__bbs_post
-- ----------------------------
CREATE TABLE IF NOT EXISTS `__PREFIX__bbs_post`  (
                                `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
                                `forum_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '板块id',
                                `thread_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '主题id',
                                `user_id` int(11) NOT NULL DEFAULT 0 COMMENT '发帖用户',
                                `floor` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '楼层',
                                `user_ip` char(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '用户ip',
                                `first_id` int(11) NULL DEFAULT 0 COMMENT '首帖id',
                                `parent_id` int(11) NOT NULL DEFAULT 0 COMMENT '父级贴子id',
                                `id_path` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT 'id路径用于无限分级',
                                `praise_number` int(11) UNSIGNED NULL DEFAULT 0 COMMENT '点赞数量',
                                `post_number` int(11) UNSIGNED NULL DEFAULT 0 COMMENT '回复数量',
                                `collect_number` int(11) UNSIGNED NULL DEFAULT 0 COMMENT '收藏数量',
                                `content_html` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL COMMENT 'html内容',
                                `content_fmt` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL COMMENT 'html转义后内容',
                                `brief` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '简略内容',
                                `parent_floor` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '父级楼层',
                                `report_number` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '被举报次数',
                                `last_user_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '最后发帖用户',
                                `last_post_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '最后帖子',
                                `last_time` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '最后回复时间',
                                `createtime` int(10) NOT NULL COMMENT '创建时间',
                                `updatetime` int(10) NOT NULL COMMENT '更新时间',
                                `deletetime` int(10) NULL DEFAULT NULL COMMENT '删除时间',
                                PRIMARY KEY (`id`) USING BTREE,
                                INDEX `thread_id`(`thread_id`) USING BTREE,
                                INDEX `parent_id`(`parent_id`) USING BTREE,
                                INDEX `id_path`(`id_path`) USING BTREE,
                                INDEX `floor`(`floor`) USING BTREE,
                                INDEX `first_id`(`first_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '帖子表' ;

-- ----------------------------
-- Table structure for __PREFIX__bbs_praise_post
-- ----------------------------
CREATE TABLE IF NOT EXISTS `__PREFIX__bbs_praise_post`  (
                                       `post_id` int(11) NOT NULL DEFAULT 0 COMMENT '帖子id',
                                       `user_id` int(11) NOT NULL DEFAULT 0 COMMENT '用户id',
                                       `createtime` int(10) NOT NULL DEFAULT 0 COMMENT '创建时间',
                                       PRIMARY KEY (`post_id`, `user_id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '帖子点赞表' ;

-- ----------------------------
-- Table structure for __PREFIX__bbs_praise_thread
-- ----------------------------
CREATE TABLE IF NOT EXISTS `__PREFIX__bbs_praise_thread`  (
                                         `thread_id` int(11) NOT NULL DEFAULT 0 COMMENT '主题id',
                                         `user_id` int(11) NOT NULL DEFAULT 0 COMMENT '用户id',
                                         `createtime` int(10) NOT NULL DEFAULT 0 COMMENT '创建时间',
                                         PRIMARY KEY (`thread_id`, `user_id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '主题点赞表' ;

-- ----------------------------
-- Table structure for __PREFIX__bbs_report
-- ----------------------------
CREATE TABLE IF NOT EXISTS `__PREFIX__bbs_report`  (
                                  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
                                  `user_id` int(11) NOT NULL COMMENT '举报用户',
                                  `type` enum('1','2') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '类型:1=主题,2=帖子',
                                  `value_id` int(11) NOT NULL DEFAULT 0 COMMENT '关联id',
                                  `value_user_id` int(11) NOT NULL DEFAULT 0 COMMENT '被举报帖子/主题创建者',
                                  `describe` varchar(60) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '描述',
                                  `status` tinyint(1) NULL DEFAULT 1 COMMENT '状态:0=忽略,1=正常',
                                  `createtime` int(10) NOT NULL DEFAULT 0 COMMENT '创建时间',
                                  `updatetime` int(10) NOT NULL COMMENT '更新时间',
                                  PRIMARY KEY (`id`) USING BTREE,
                                  INDEX `user_id`(`user_id`) USING BTREE,
                                  INDEX `value_user_id`(`value_user_id`) USING BTREE,
                                  INDEX `value_id`(`value_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '举报表' ;



-- ----------------------------
-- Table structure for __PREFIX__bbs_collect_post
-- ----------------------------
CREATE TABLE IF NOT EXISTS `__PREFIX__bbs_collect_post`  (
    `post_id` int(11) NOT NULL DEFAULT 0 COMMENT '帖子id',
    `user_id` int(11) NOT NULL COMMENT '用户id',
    `createtime` int(10) NOT NULL COMMENT '创建时间',
    PRIMARY KEY (`post_id`, `user_id`) USING BTREE
    ) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '帖子收藏表' ;

-- ----------------------------
-- Table structure for __PREFIX__bbs_collect_thread
-- ----------------------------
CREATE TABLE IF NOT EXISTS `__PREFIX__bbs_collect_thread`  (
    `thread_id` int(11) NOT NULL DEFAULT 0 COMMENT '主题id',
    `user_id` int(11) NOT NULL COMMENT '用户id',
    `createtime` int(10) NOT NULL COMMENT '创建时间',
    PRIMARY KEY (`thread_id`, `user_id`) USING BTREE
    ) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '主题收藏表' ;
