<?php

/**
 * 助手函数：请求
 */

/**
 * 构建带参数的URL
 * @param string $url
 * @param string $path
 * @param array  $queryData
 * @return string
 * @throws Exception
 * @example
 * httpBuildUrl('https://example.com', 'api/users', ['page' => 1, 'limit' => 10]);
 * // "https://example.com/api/users?page=1&limit=10"
 * httpBuildUrl('https://example.com/api?old=1', '', ['new' => 2]);
 * // "https://example.com/api?old=1&new=2"
 * @author siushin<siushin@163.com>
 */
function httpBuildUrl(string $url, string $path = '', array $queryData = []): string
{
    $parsedUrl = parse_url($url);

    $existingQueryParams = [];
    if (isset($parsedUrl['query'])) {
        parse_str($parsedUrl['query'], $existingQueryParams);
    }

    $mergedQueryParams = array_merge($existingQueryParams, $queryData);

    // 使用http_build_query生成新的查询字符串
    $newQueryString = http_build_query($mergedQueryParams);

    // 重新构建URL（不包含查询字符串）
    $url_path = explode('/', trim($parsedUrl['path'] ?? '', '/'));
    $path = explode('/', trim($path, '/'));
    // 合并path重组，并去掉尾部斜杆/
    $full_path = rtrim('/' . ltrim(implode('/', array_merge($url_path, $path)), '/'), '/');

    if (!isset($parsedUrl['scheme']) || !isset($parsedUrl['host'])) {
        throw_exception('无效的URL：缺少协议或主机');
    }

    $baseUrl = $parsedUrl['scheme'] . '://' . $parsedUrl['host'] . $full_path;

    return $newQueryString ? $baseUrl . '?' . $newQueryString : $baseUrl;
}

/**
 * 处理curl请求
 * @param       $ch
 * @param array $headers
 * @param array $extend_data
 * @return array
 * @throws Exception
 * @example
 * $ch = curl_init('https://api.example.com/data');
 * $headers = ['Authorization' => 'Bearer token123'];
 * $extend = ['cookie_file' => '/tmp/cookies.txt'];
 * $result = baseHandleCurl($ch, $headers, $extend);
 * // 返回解析后的JSON数组
 * @author siushin<siushin@163.com>
 */
function baseHandleCurl($ch, array $headers = [], array $extend_data = []): array
{
    $cookie_file = $extend_data['cookie_file'] ?? null;

    $defaultHeaders = ["Accept: */*"];
    $headers = array_map(fn($key, $value) => $key . ': ' . $value, array_keys($headers), array_values($headers));
    $allHeaders = array_merge($defaultHeaders, $headers); // 合并默认头和自定义头

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // 将curl_exec()获取的信息以文件流的形式返回，而不是直接输出。
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $allHeaders); // 设置HTTP头
    curl_setopt($ch, CURLOPT_HEADER, false); // 不需要响应头

    if ($cookie_file !== null) {
        curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie_file);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_file);
    }

    $response = curl_exec($ch);

    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    // 检查是否有错误发生
    if ($httpCode != 200) {
        $errMsg = '';
        if (curl_errno($ch)) {
            $errMsg = curl_error($ch);
        } else if (!json_validate($response)) {
            $errMsg = strip_tags($response);
        }
        if ($errMsg) {
            throw new Exception('[remote]' . $errMsg);
        }
    }

    if (!json_validate($response)) {
        throw new Exception('[remote]' . $response);
    }

    curl_close($ch);

    return json_decode($response, true);
}

/**
 * Get请求
 * @param string $url       请求URL
 * @param array  $queryData query参数
 * @param array  $headers
 * @param array  $extend_data
 * @return array
 * @throws Exception
 * @example
 * $result = httpGet('https://api.example.com/users', ['page' => 1, 'limit' => 10]);
 * // 发送 GET 请求到 https://api.example.com/users?page=1&limit=10
 * $result = httpGet('https://api.example.com/data', [], ['Authorization' => 'Bearer token']);
 * // 带自定义请求头
 * @author siushin<siushin@163.com>
 */
function httpGet(string $url, array $queryData = [], array $headers = [], array $extend_data = []): array
{
    $url = httpBuildUrl($url, '', $queryData);

    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, $url);

    return baseHandleCurl($ch, $headers, $extend_data);
}

/**
 * Post请求
 * @param string $url        请求URL
 * @param array  $postData   post参数
 * @param array  $headers
 * @param string $field_type 请求参数类型（支持query、json，默认为post）
 * @param array  $extend_data
 * @return array
 * @throws Exception
 * @example
 * // Form表单提交
 * httpPost('https://api.example.com/users', ['name' => 'Tom', 'age' => 20], [], 'form');
 * // JSON提交
 * httpPost('https://api.example.com/users', ['name' => 'Tom'], [], 'json');
 * // Query字符串提交
 * httpPost('https://api.example.com/users', ['name' => 'Tom'], [], 'query');
 * @author siushin<siushin@163.com>
 */
function httpPost(string $url, array $postData = [], array $headers = [], string $field_type = 'form', array $extend_data = []): array
{
    if ($field_type == 'json') {
        $postData = json_encode($postData);
        $headers['Content-Type'] = 'application/json; charset=utf-8';
    } else if ($field_type == 'query') {
        $postData = http_build_query($postData);
    } else if ($field_type == 'form') {
        $headers['Content-Type'] = 'multipart/form-data; charset=utf-8';
    }

    $ch = curl_init();

    // 设置错误处理函数
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true); // 发送一个POST请求
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);

    return baseHandleCurl($ch, $headers, $extend_data);
}

/**
 * Post请求（query方式）
 * @param string $url
 * @param array  $queryData
 * @param array  $headers
 * @param array  $extend_data
 * @return array
 * @throws Exception
 * @example
 * httpQueryPost('https://api.example.com/users', ['name' => 'Tom', 'age' => 20]);
 * // POST 请求，参数以 query string 形式发送
 * @author siushin<siushin@163.com>
 */
function httpQueryPost(string $url, array $queryData = [], array $headers = [], array $extend_data = []): array
{
    return httpPost(httpBuildUrl($url, '', $queryData), [], $headers, 'query', $extend_data);
}

/**
 * Post请求（JSON方式）
 * @param string $url
 * @param array  $jsonData
 * @param array  $headers
 * @param array  $extend_data
 * @return array
 * @throws Exception
 * @example
 * httpJsonPost('https://api.example.com/users', ['name' => 'Tom', 'age' => 20]);
 * // POST 请求，数据以 JSON 格式发送，Content-Type: application/json
 * httpJsonPost('https://api.example.com/users', ['name' => 'Tom'], ['X-API-Key' => 'secret']);
 * // 带自定义请求头
 * @author siushin<siushin@163.com>
 */
function httpJsonPost(string $url, array $jsonData = [], array $headers = [], array $extend_data = []): array
{
    return httpPost($url, $jsonData, $headers, 'json', $extend_data);
}