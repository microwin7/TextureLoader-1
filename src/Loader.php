<?php

namespace Gravita\JsonTextureProvider;

use TypeError;

class Loader
{
    private readonly DAO $dao;
    public function __construct(DAO $dao)
    {
        $this->dao = $dao;
    }

    public function get(string $uuid): \ArrayObject
    {
        return $this->dao->getAllByUserUuid($uuid);
    }

    public function upload(string $uuid, $fileinfo, $options, string $assetType, UploadConfiguration $config): \ArrayObject
    {
        if ($fileinfo['size'] > $config->maxUploadSize) {
            throw new LoaderException("Image too big: Size limit");
        }

        $content = file_get_contents($fileinfo['tmp_name']);

        $size = getimagesizefromstring($content);

        if (!$size) {
            throw new LoaderException("Upload file not a image");
        }

        $width = $size[0];
        $height = $size[1];
        $imgType = $size[2];

        if ($imgType != IMAGETYPE_PNG) {
            throw new LoaderException("Image is not a png format");
        }

        if ($height > $config->maxUploadHeight) {
            throw new LoaderException("Image too big: Height limit");
        }

        if ($width > $config->maxUploadWidth) {
            throw new LoaderException("Image too big: Width limit");
        }
        $hash = hash("sha256", $content);
        $filePath = $config->getBaseDir() . $hash . ".png";

        if (!file_exists($filePath)) {
            file_put_contents($filePath, $content);
        }
        if ($assetType == 'SKIN' && $config->generateAvatar) {
            $scale = (int)($width / 64);
            $this->getAvatar($config->getBaseDir(), function () use ($content) {
                return $content;
            }, $hash, $uuid, $scale);
        }
        $metadata = [];
        if ($assetType == "SKIN" && $options["modelSlim"] == true) {
            $metadata["model"] = "slim";
        }
        $metadata_json = json_encode($metadata);
        $this->dao->update($uuid, $assetType, $hash, $metadata_json);
        return new \ArrayObject([
            "url" => $config->getBaseUrl() . $hash . ".png",
            "digest" => $hash,
            "metadata" => $metadata
        ]);
    }

    public function getAvatar(string $baseDir, callable $skinImageGetter, string $skinHash, string $uuid, int $scale): string
    {
        $skinSize = $scale * 8;
        $avatarHash = $this->dao->getAvatarHashBySkinHash($skinHash, $skinSize);
        if ($avatarHash !== null) {
            $tmpFile = fopen("php://temp", "rwb");
            if ($tmpFile === false) throw new TypeError('Cannot Initialize new stream read-write in php://temp');
            $image = imagecreatefromstring($skinImageGetter());
            if ($image === false) throw new TypeError('Error reading data. This file is not an image.');
            $newImage = imagecreatetruecolor($skinSize, $skinSize);
            if ($newImage === false) throw new TypeError('Cannot Initialize new GD image stream');
            imagecopyresized($newImage, $image, 0, 0, $skinSize, $skinSize, $skinSize, $skinSize, $skinSize, $skinSize);
            imagecopyresized($newImage, $image, 0, 0, 5 * $skinSize, $skinSize, $skinSize, $skinSize, $skinSize, $skinSize);
            imagepng($newImage, $tmpFile);
            imagedestroy($image);
            imagedestroy($newImage);
            fseek($tmpFile, 0);
            $avatarContent = fread($tmpFile, fstat($tmpFile)['size']);
            $avatarHash = hash("sha256", $avatarContent);
            $avatarFilePath = $baseDir . $avatarHash . ".png";
            file_put_contents($avatarFilePath, $avatarContent);
            $this->dao->updateAvatarCache($skinHash, $avatarHash, $skinSize);
        }
        $this->dao->update($uuid, "AVATAR", $avatarHash, "{}");
        return $baseDir . $avatarHash . ".png";
    }
}
