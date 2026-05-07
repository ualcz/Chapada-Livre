@php
    $reactIndexPath = public_path('react/index.html');
    if (file_exists($reactIndexPath)) {
        $content = file_get_contents($reactIndexPath);
        // Garante que os caminhos para assets estejam corretos se necessário
        echo $content;
    } else {
        echo "React build not found. Please run 'npm run build' in classifieds-connect.";
    }
@endphp
