<?php
/**
 * Created by mr.vjcspy@gmail.com - khoild@smartosc.com.
 * Date: 19/01/2017
 * Time: 17:04
 */

namespace SM\XRetail\Controller\V1;


use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ResponseInterface;

/**
 * Class Uploader
 *
 * @package SM\XRetail\Controller\V1
 */
class Uploader extends \Magento\Framework\App\Action\Action {

    /**
     * @var \Magento\MediaStorage\Model\File\UploaderFactory
     */
    protected $uploaderFactory;
    /**
     * @var \Magento\Framework\App\Filesystem\DirectoryList
     */
    protected $directoryList;
    /**
     * @var \Magento\Framework\Filesystem
     */
    protected $fileSystem;
    private   $config;

    /**
     * Uploader constructor.
     *
     * @param \Magento\Framework\App\Action\Context            $context
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Magento\Framework\App\Filesystem\DirectoryList  $directory_list
     * @param \Magento\MediaStorage\Model\File\UploaderFactory $uploaderFactory
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Framework\App\Filesystem\DirectoryList $directory_list,
        \Magento\MediaStorage\Model\File\UploaderFactory $uploaderFactory,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\PageCache\Model\Config $config
    ) {
        $this->config          = $config;
        $this->fileSystem      = $filesystem;
        $this->uploaderFactory = $uploaderFactory;
        $this->directoryList   = $directory_list;
        parent::__construct($context);
    }


    public function execute() {
        /** @var \Magento\Framework\App\Request\Http $request */
        $request = $this->getRequest();
        /** @var \Magento\Framework\App\Response\Http $response */
        $response = $this->getResponse();

        /*See Note: Magento 2 Full Page caching */
        if ($this->config->isEnabled()){
            /*
             * Fix for magento 2ee:
             * \Magento\Framework\App\PageCache\Kernel::process
             * */
            $response->setPublicHeaders($this->config->getTtl());
        }

        if ($request->isOptions()) {
            return $response->clearHeaders()
                            ->setHeader('Content-Type', 'application/x-www-form-urlencoded')
                            ->setHeader("Access-Control-Allow-Origin", $_SERVER['HTTP_ORIGIN'])
                            ->setHeader("Access-Control-Allow-Credentials", 'true')
                            ->setHeader("Access-Control-Allow-Methods", "PUT,POST,PATCH,DELETE")
                            ->setHeader("Cache-Control", "no-cache, no-store, must-revalidate")
                            ->setHeader(
                                "Access-Control-Allow-Headers",
                                "Content-Type, Content-Range, Content-Disposition, Content-Description, Authorization, X-Requested-With,Authorization-Code,Access-Control-Allow-Origin")
                            ->setHttpResponseCode(200);
        }
        else {
            $fileName = $this->uploadFileAndGetName();
            $fileName = $this->_objectManager->get('Magento\Store\Model\StoreManagerInterface')
                                             ->getStore()
                                             ->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'images/' . $fileName;

            return $response->setHeader('Content-Type', 'application/x-www-form-urlencoded')
                            ->setHeader("Access-Control-Allow-Origin", $_SERVER['HTTP_ORIGIN'])
                            ->setHeader("Access-Control-Allow-Credentials", 'true')
                            ->setHeader("Access-Control-Allow-Methods", "PUT,POST,PATCH,DELETE")
                            ->setHeader(
                                "Access-Control-Allow-Headers",
                                "Content-Type, Content-Range, Content-Disposition, Content-Description, Authorization, X-Requested-With,Authorization-Code,Access-Control-Allow-Origin")
                            ->setHttpResponseCode(200)
                            ->setBody($fileName);
        }
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function uploadFileAndGetName() {
        try {
            $uploader = $this->uploaderFactory->create(['fileId' => 'file']);
            /** test The File with Callback here */
            $uploader->setAllowedExtensions(['jpg', 'png', 'jpeg']);
            $uploader->setAllowRenameFiles(true);
            $uploader->setFilesDispersion(false);
            $uploader->setAllowCreateFolders(true);
            $result   = $uploader->save(
                $this->fileSystem->getDirectoryRead(DirectoryList::MEDIA)
                                 ->getAbsolutePath('images/'));
            $fileName = @$result['file'];

            return $fileName;
        }
        catch (\Exception $e) {
            if ($e->getCode() != \Magento\Framework\File\Uploader::TMP_NAME_EMPTY) {
                throw new \Exception($e->getMessage());
            }
        }

        return '';
    }

    /**
     * @return \Magento\Framework\App\Filesystem\DirectoryList
     */
    public function getDirectoryList() {
        return $this->directoryList;
    }
}