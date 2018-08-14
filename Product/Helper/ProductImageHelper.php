<?php
/**
 * Created by KhoiLe - mr.vjcspy@gmail.com
 * Date: 7/22/17
 * Time: 10:52 AM
 */

namespace SM\Product\Helper;


use Magento\Framework\App\Filesystem\DirectoryList;

class ProductImageHelper extends \Magento\Framework\App\Helper\AbstractHelper {

    /**
     * Custom directory relative to the "media" folder
     */
    const DIRECTORY         = 'retail/pos';
    const CATALOG_DIRECTORY = '/catalog/product';

    /**
     * @var \Magento\Framework\Filesystem\Directory\WriteInterface
     */
    protected $_mediaDirectory;

    /**
     * @var \Magento\Framework\Image\Factory
     */
    protected $_imageFactory;

    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;
    /**
     * @var \Magento\Catalog\Model\Product\Media\Config
     */
    private $productMediaConfig;
    /**
     * @var \Magento\Framework\App\Filesystem\DirectoryList
     */
    private $directoryList;
    /**
     * @var \Magento\Framework\Filesystem\Directory\WriteInterface
     */
    private $mediaDirectoryRead;

    /**
     * @param \Magento\Framework\App\Helper\Context       $context
     * @param \Magento\Framework\Filesystem               $filesystem
     * @param \Magento\Framework\Image\AdapterFactory     $imageFactory
     * @param \Magento\Store\Model\StoreManagerInterface  $storeManager
     * @param \Magento\Catalog\Model\Product\Media\Config $productMediaConfig
     *
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\Image\AdapterFactory $imageFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\Product\Media\Config $productMediaConfig
    ) {
        $this->_mediaDirectory    = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $this->mediaDirectoryRead = $filesystem->getDirectoryRead(DirectoryList::MEDIA);
        $this->_imageFactory      = $imageFactory;
        $this->_storeManager      = $storeManager;
        $this->productMediaConfig = $productMediaConfig;
        parent::__construct($context);
    }

    /**
     * First check this file on FS
     *
     * @param string $filename
     *
     * @return bool
     */
    protected function _fileExists($filename) {
        if ($this->_mediaDirectory->isFile($filename)) {
            return true;
        }

        return false;
    }

    /**
     * Resize image
     *
     * @param          $image
     * @param int      $width
     * @param null     $height
     *
     * @return string
     */
    public function resize($image, $width = 200, $height = null) {
        $mediaFolder = self::DIRECTORY;

        $path = $mediaFolder . '/cache';
        if ($width !== null) {
            $path .= '/' . $width . 'x';
            if ($height !== null) {
                $path .= $height;
            }
        }

        $absolutePath = $this->mediaDirectoryRead->getAbsolutePath(self::CATALOG_DIRECTORY . $image);
        $imageResized = $this->_mediaDirectory->getAbsolutePath($path) . $image;

        if (!$this->_fileExists($path . $image)) {
            if ($this->_fileExists(self::CATALOG_DIRECTORY . $image)) {
                $imageFactory = $this->_imageFactory->create();
                $imageFactory->open($absolutePath);
                $imageFactory->constrainOnly(true);
                $imageFactory->keepTransparency(true);
                $imageFactory->keepFrame(false);
                $imageFactory->keepAspectRatio(true);
                $imageFactory->resize($width, $height);
                $imageFactory->save($imageResized);
            }
            else {
                return "";
            }
        }

        return $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . $path . $image;
    }

    public function getImageUrl(\Magento\Catalog\Model\Product $product) {
        if (is_null($product->getImage()) || $product->getImage() == 'no_selection' || !$product->getImage()) {
            $imageUrl = null;
        }
        else {
            $imageUrl = $this->resize($product->getImage());
            //$xProduct->setData('origin_image', $this->getProductMediaConfig()->getMediaUrl($product->getImage()));
        }

        return $imageUrl;
    }
}
