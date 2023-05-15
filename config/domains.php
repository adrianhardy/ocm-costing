<?php

return [
    'uk' => explode(',', strtolower(env('DOMAINS_UK', 'ukcosting.orchardcrownmall.co.uk,ukcoststaging.orchardcrownmall.co.uk'))),
    'cn' => explode(',', strtolower(env('DOMAINS_CN', 'cncosting.orchardcrownmall.co.uk,cncoststaging.orchardcrownmall.co.uk'))),
];