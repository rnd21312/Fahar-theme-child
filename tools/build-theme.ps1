[CmdletBinding()]
param(
    [switch] $Clean,
    [string] $OutputDirectory = 'build'
)

Set-StrictMode -Version Latest
$ErrorActionPreference = 'Stop'

$themeSlug = 'fahar-theme-child'
$repoRoot = [IO.Path]::GetFullPath((Join-Path $PSScriptRoot '..')).TrimEnd([char[]] @('\', '/'))
$stylePath = Join-Path $repoRoot 'style.css'
$temporaryArchivePath = $null
$finalArchivePath = $null
$finalArchiveWritten = $false
$excludedDirectoryNames = @(
    '.git', '.github', '.agents', '.vscode', '.idea', '.codex',
    'build', 'dist', 'release', 'tools', 'docs', 'tests',
    'node_modules', 'vendor', 'coverage', '.cache', '.tmp', 'tmp', 'temp',
    '.phpunit.cache'
)
$excludedRootFiles = @(
    '.gitignore', '.gitattributes', '.editorconfig', 'AGENTS.md',
    'CODEX_BOOTSTRAP_FAHAR_THEME.md', 'wp-config.php', 'wp-config-local.php', '.htaccess'
)
$excludedFileNames = @(
    '.DS_Store', '.AppleDouble', '.LSOverride', 'Thumbs.db', 'Thumbs.db:encryptable',
    'ehthumbs.db', 'Desktop.ini', '.phpunit.result.cache'
)

function Test-PathWithinDirectory {
    param(
        [Parameter(Mandatory = $true)][string] $Path,
        [Parameter(Mandatory = $true)][string] $Directory
    )
    $fullPath = [IO.Path]::GetFullPath($Path)
    $fullDirectory = [IO.Path]::GetFullPath($Directory).TrimEnd([char[]] @('\', '/'))
    return $fullPath.StartsWith(
        $fullDirectory + [IO.Path]::DirectorySeparatorChar,
        [StringComparison]::OrdinalIgnoreCase
    )
}

function Test-ExcludedThemeFile {
    param([Parameter(Mandatory = $true)][string] $RelativePath)
    $normalizedPath = $RelativePath.Replace('\', '/')
    $segments = @($normalizedPath.Split([char] '/'))
    $fileName = $segments[-1]

    foreach ($segment in $segments) {
        if ($excludedDirectoryNames -contains $segment) { return $true }
    }
    if (($segments.Count -eq 1) -and ($excludedRootFiles -contains $fileName)) { return $true }
    if ($excludedFileNames -contains $fileName) { return $true }
    if (($fileName -eq '.env') -or ($fileName -like '.env.*')) { return $true }
    if (($fileName -like 'wp-config*.php') -or
        ($fileName -like '*.swp') -or ($fileName -like '*.swo') -or
        ($fileName -like '*~') -or ($fileName -like '*.log') -or
        ($fileName -like '*.tmp') -or ($fileName -like '*.temp') -or
        ($fileName -like '*.bak') -or ($fileName -like '*.old') -or
        ($fileName -like '*.zip')) { return $true }
    return $false
}

function Test-ThemeArchive {
    param([Parameter(Mandatory = $true)][string] $ArchivePath)
    if (-not [IO.File]::Exists($ArchivePath)) { throw "Archive was not created: $ArchivePath" }
    if ((Get-Item -LiteralPath $ArchivePath).Length -le 0) { throw "Archive is empty: $ArchivePath" }

    $archiveStream = $null
    $archive = $null
    try {
        $archiveStream = [IO.File]::Open(
            $ArchivePath, [IO.FileMode]::Open, [IO.FileAccess]::Read, [IO.FileShare]::Read
        )
        $archive = New-Object IO.Compression.ZipArchive(
            $archiveStream, [IO.Compression.ZipArchiveMode]::Read, $false
        )
        $entryNames = @($archive.Entries | ForEach-Object { $_.FullName })
        if ($entryNames.Count -eq 0) { throw 'Archive contains no files.' }

        foreach ($requiredEntry in @("$themeSlug/style.css", "$themeSlug/functions.php")) {
            if ($entryNames -cnotcontains $requiredEntry) {
                throw "Archive is missing required file: $requiredEntry"
            }
        }

        $rootPrefix = "$themeSlug/"
        foreach ($entryName in $entryNames) {
            if (($entryName -notlike "$rootPrefix*") -or $entryName.Contains('\') -or
                $entryName.StartsWith('/') -or $entryName.Contains(':')) {
                throw "Unsafe or unexpected archive path: $entryName"
            }
            $innerPath = $entryName.Substring($rootPrefix.Length)
            $innerSegments = @($innerPath.Split([char] '/'))
            if (($innerPath.Length -eq 0) -or ($innerSegments -contains '..') -or
                ($innerSegments -contains '.') -or
                (Test-ExcludedThemeFile -RelativePath $innerPath)) {
                throw "Development or unsafe path found in archive: $entryName"
            }
        }
        return $entryNames.Count
    }
    finally {
        if ($null -ne $archive) { $archive.Dispose() }
        if ($null -ne $archiveStream) { $archiveStream.Dispose() }
    }
}

try {
    Add-Type -AssemblyName System.IO.Compression -ErrorAction Stop
    if (-not [IO.File]::Exists($stylePath)) { throw "Theme stylesheet not found: $stylePath" }

    $styleContent = [IO.File]::ReadAllText($stylePath)
    $headerMatch = [regex]::Match($styleContent, '\A\s*/\*(?<header>.*?)\*/', 'Singleline')
    if (-not $headerMatch.Success) { throw 'The style.css WordPress theme header is missing.' }
    $versionMatch = [regex]::Match(
        $headerMatch.Groups['header'].Value,
        '^\s*\*?\s*Version\s*:\s*(?<version>[^\r\n]+?)\s*$',
        [Text.RegularExpressions.RegexOptions]::IgnoreCase -bor
            [Text.RegularExpressions.RegexOptions]::Multiline
    )
    if (-not $versionMatch.Success) { throw 'The Version header is missing or empty in style.css.' }
    $version = $versionMatch.Groups['version'].Value.Trim()
    if ([string]::IsNullOrWhiteSpace($version) -or
        ($version.IndexOfAny([IO.Path]::GetInvalidFileNameChars()) -ge 0)) {
        throw "The style.css Version header is invalid: '$version'"
    }

    if ([IO.Path]::IsPathRooted($OutputDirectory)) {
        $outputPath = [IO.Path]::GetFullPath($OutputDirectory)
    } else {
        $outputPath = [IO.Path]::GetFullPath((Join-Path $repoRoot $OutputDirectory))
    }
    $outputPath = $outputPath.TrimEnd([char[]] @('\', '/'))
    if ([string]::IsNullOrWhiteSpace($outputPath) -or
        $outputPath.Equals($repoRoot, [StringComparison]::OrdinalIgnoreCase) -or
        $outputPath.Equals([IO.Path]::GetPathRoot($outputPath), [StringComparison]::OrdinalIgnoreCase)) {
        throw "Unsafe output directory: $outputPath"
    }

    if ($Clean -and [IO.Directory]::Exists($outputPath)) {
        Get-ChildItem -LiteralPath $outputPath -Force | Remove-Item -Recurse -Force
    }
    [IO.Directory]::CreateDirectory($outputPath) | Out-Null
    $finalArchivePath = Join-Path $outputPath "$themeSlug-$version.zip"
    $temporaryArchivePath = Join-Path $outputPath ('.{0}-{1}-{2}.tmp' -f $themeSlug, $version, [guid]::NewGuid().ToString('N'))

    $sourceFiles = @(
        [IO.Directory]::EnumerateFiles($repoRoot, '*', [IO.SearchOption]::AllDirectories) |
            Where-Object {
                $fullPath = [IO.Path]::GetFullPath($_)
                $relativePath = $fullPath.Substring($repoRoot.Length).TrimStart([char[]] @('\', '/'))
                (-not (Test-PathWithinDirectory -Path $fullPath -Directory $outputPath)) -and
                    (-not (Test-ExcludedThemeFile -RelativePath $relativePath))
            } | Sort-Object
    )
    if ($sourceFiles.Count -eq 0) { throw 'No distributable theme files were found.' }

    $zipStream = $null
    $zipArchive = $null
    try {
        $zipStream = [IO.File]::Open(
            $temporaryArchivePath, [IO.FileMode]::CreateNew,
            [IO.FileAccess]::ReadWrite, [IO.FileShare]::None
        )
        $zipArchive = New-Object IO.Compression.ZipArchive(
            $zipStream, [IO.Compression.ZipArchiveMode]::Create, $true
        )
        foreach ($sourcePath in $sourceFiles) {
            $relativePath = $sourcePath.Substring($repoRoot.Length).TrimStart([char[]] @('\', '/')).Replace('\', '/')
            $entry = $zipArchive.CreateEntry(
                "$themeSlug/$relativePath", [IO.Compression.CompressionLevel]::Optimal
            )
            $lastWriteTime = [DateTimeOffset] [IO.File]::GetLastWriteTimeUtc($sourcePath)
            $minimumZipTime = [DateTimeOffset]::Parse('1980-01-01T00:00:00Z')
            $maximumZipTime = [DateTimeOffset]::Parse('2107-12-31T23:59:58Z')
            if ($lastWriteTime -lt $minimumZipTime) { $lastWriteTime = $minimumZipTime }
            elseif ($lastWriteTime -gt $maximumZipTime) { $lastWriteTime = $maximumZipTime }
            $entry.LastWriteTime = $lastWriteTime

            $sourceStream = $null
            $entryStream = $null
            try {
                $sourceStream = [IO.File]::OpenRead($sourcePath)
                $entryStream = $entry.Open()
                $sourceStream.CopyTo($entryStream)
            }
            finally {
                if ($null -ne $entryStream) { $entryStream.Dispose() }
                if ($null -ne $sourceStream) { $sourceStream.Dispose() }
            }
        }
    }
    finally {
        if ($null -ne $zipArchive) { $zipArchive.Dispose() }
        if ($null -ne $zipStream) { $zipStream.Dispose() }
    }

    $fileCount = Test-ThemeArchive -ArchivePath $temporaryArchivePath
    if ([IO.File]::Exists($finalArchivePath)) { [IO.File]::Delete($finalArchivePath) }
    [IO.File]::Move($temporaryArchivePath, $finalArchivePath)
    $temporaryArchivePath = $null
    $finalArchiveWritten = $true
    $fileCount = Test-ThemeArchive -ArchivePath $finalArchivePath
    $archiveSize = (Get-Item -LiteralPath $finalArchivePath).Length
    $displayPath = $finalArchivePath
    if (Test-PathWithinDirectory -Path $finalArchivePath -Directory $repoRoot) {
        $displayPath = $finalArchivePath.Substring($repoRoot.Length).TrimStart([char[]] @('\', '/'))
    }
    Write-Output "Theme:   $themeSlug"
    Write-Output "Version: $version"
    Write-Output "Files:   $fileCount"
    Write-Output "Built:   $displayPath ($archiveSize bytes)"
}
catch {
    if (($null -ne $temporaryArchivePath) -and [IO.File]::Exists($temporaryArchivePath)) {
        [IO.File]::Delete($temporaryArchivePath)
    }
    if ($finalArchiveWritten -and ($null -ne $finalArchivePath) -and [IO.File]::Exists($finalArchivePath)) {
        [IO.File]::Delete($finalArchivePath)
    }
    Write-Error "Theme build failed: $($_.Exception.Message)"
    exit 1
}
