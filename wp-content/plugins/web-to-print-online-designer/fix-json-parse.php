<?php
/**
 * 批量修复 app-modern.min.js 中的 JSON.parse(data) 问题
 * 这个脚本会自动修复所有需要修复的地方
 */

// 设置文件路径
$file_path = __DIR__ . '/assets/js/app-modern.min.js';

if (!file_exists($file_path)) {
    die("文件不存在: $file_path\n");
}

echo "开始修复 JSON.parse(data) 问题...\n";
echo "文件路径: $file_path\n\n";

// 读取文件内容
$content = file_get_contents($file_path);
$original_content = $content;

// 定义需要修复的模式和替换内容
$fixes = [
    // 模式1: data = JSON.parse(data);
    [
        'pattern' => '/\s+data\s*=\s*JSON\.parse\(data\);/',
        'replacement' => "\n            // 修复：检查 data 是否已经是对象，如果是则直接使用，否则尝试解析\n            if (typeof data === 'string') {\n                try {\n                    data = JSON.parse(data);\n                } catch (e) {\n                    console.error('JSON.parse 失败:', e, '原始数据:', data);\n                    return;\n                }\n            }",
        'description' => '修复 data = JSON.parse(data); 模式'
    ],
    
    // 模式2: var _data = JSON.parse(data);
    [
        'pattern' => '/\s+var\s+_data\s*=\s*JSON\.parse\(data\);/',
        'replacement' => "\n            // 修复：检查 data 是否已经是对象，如果是则直接使用，否则尝试解析\n            var _data;\n            if (typeof data === 'string') {\n                try {\n                    _data = JSON.parse(data);\n                } catch (e) {\n                    console.error('JSON.parse 失败:', e, '原始数据:', data);\n                    return;\n                }\n            } else {\n                _data = data; // data 已经是对象，直接使用\n            }",
        'description' => '修复 var _data = JSON.parse(data); 模式'
    ],
    
    // 模式3: var data = JSON.parse(data);
    [
        'pattern' => '/\s+var\s+data\s*=\s*JSON\.parse\(data\);/',
        'replacement' => "\n            // 修复：检查 data 是否已经是对象，如果是则直接使用，否则尝试解析\n            if (typeof data === 'string') {\n                try {\n                    data = JSON.parse(data);\n                } catch (e) {\n                    console.error('JSON.parse 失败:', e, '原始数据:', data);\n                    return;\n                }\n            }",
        'description' => '修复 var data = JSON.parse(data); 模式'
    ],
    
    // 模式4: _data = JSON.parse(data);
    [
        'pattern' => '/\s+_data\s*=\s*JSON\.parse\(data\);/',
        'replacement' => "\n            // 修复：检查 data 是否已经是对象，如果是则直接使用，否则尝试解析\n            if (typeof data === 'string') {\n                try {\n                    _data = JSON.parse(data);\n                } catch (e) {\n                    console.error('JSON.parse 失败:', e, '原始数据:', data);\n                    return;\n                }\n            } else {\n                _data = data; // data 已经是对象，直接使用\n            }",
        'description' => '修复 _data = JSON.parse(data); 模式'
    ]
];

$total_fixes = 0;

// 应用所有修复
foreach ($fixes as $index => $fix) {
    echo "应用修复 " . ($index + 1) . ": {$fix['description']}\n";
    
    // 计算修复前的匹配数量
    $before_count = preg_match_all($fix['pattern'], $content, $matches);
    
    if ($before_count > 0) {
        // 应用修复
        $new_content = preg_replace($fix['pattern'], $fix['replacement'], $content);
        
        if ($new_content !== null) {
            $content = $new_content;
            $total_fixes += $before_count;
            echo "  ✓ 修复了 $before_count 处\n";
        } else {
            echo "  ✗ 修复失败\n";
        }
    } else {
        echo "  - 没有找到匹配的模式\n";
    }
}

// 检查是否还有其他 JSON.parse(data) 需要手动修复
$remaining_patterns = [
    '/JSON\.parse\(data\)/',
    '/data\s*=\s*JSON\.parse/',
    '/_data\s*=\s*JSON\.parse/',
    '/var\s+.*\s*=\s*JSON\.parse/'
];

echo "\n检查剩余的 JSON.parse 模式:\n";
foreach ($remaining_patterns as $pattern) {
    $count = preg_match_all($pattern, $content, $matches);
    if ($count > 0) {
        echo "  发现 $count 处: $pattern\n";
        // 显示前几个匹配
        for ($i = 0; $i < min(3, $count); $i++) {
            $line_number = substr_count(substr($content, 0, strpos($content, $matches[0][$i])), "\n") + 1;
            echo "    第 $line_number 行附近: " . substr($matches[0][$i], 0, 100) . "...\n";
        }
    }
}

// 如果有内容变化，保存文件
if ($content !== $original_content) {
    // 创建备份
    $backup_path = $file_path . '.backup.' . date('Y-m-d-H-i-s');
    if (copy($file_path, $backup_path)) {
        echo "\n✓ 已创建备份文件: " . basename($backup_path) . "\n";
    }
    
    // 保存修复后的文件
    if (file_put_contents($file_path, $content)) {
        echo "✓ 已保存修复后的文件\n";
        echo "✓ 总共修复了 $total_fixes 处 JSON.parse(data) 问题\n";
    } else {
        echo "✗ 保存文件失败\n";
    }
} else {
    echo "\n没有发现需要修复的内容\n";
}

echo "\n修复完成！\n";

// 显示修复统计
echo "\n修复统计:\n";
echo "- 原始文件大小: " . number_format(strlen($original_content)) . " 字节\n";
echo "- 修复后文件大小: " . number_format(strlen($content)) . " 字节\n";
echo "- 修复数量: $total_fixes\n";

// 提供手动检查建议
echo "\n建议:\n";
echo "1. 检查修复后的文件是否正常工作\n";
echo "2. 测试相关的 AJAX 功能\n";
echo "3. 如果发现问题，可以使用备份文件恢复\n";
echo "4. 检查浏览器控制台是否还有 JSON.parse 错误\n";
?>
