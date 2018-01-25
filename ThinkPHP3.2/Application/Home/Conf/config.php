<?php
return array(
	'LAYOUT_ON' => true,
    'DATAITEM_PARSE_FORMAT' => "linedelimiter:enter\nitemdelimiter:|",
    'ARCHIVE_STATUS' => array(
        0 => '未发布',
        1 => '已发布',
        2 => '下架',
        3 => '删除'
    ),
    'INDEX_DATAITEMS' => array(
        'GAMEID' => 6,
        'GAMEMCKEY' => 'gamesv2', //首页游戏memcache key
        'THREADKEY' => 'threadsv2', //首页帖子key
        'VIDEOID' => 16, //视频
        'VIDEOKEY' => 'index_video' //首页视频缓存key
    ),
    'FUNNYIMG_CACHE' => array(
        'CATEGORY_CACHE_KEY' => 'funnyimg_categories' //囧图分类缓存key
    )
);