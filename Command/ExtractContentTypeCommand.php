<?php

namespace SQLI\EzToolboxBundle\Command;

use SQLI\EzToolboxBundle\Services\ExtractHelper;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ExtractContentTypeCommand extends ContainerAwareCommand
{
    protected $log;
    /** @var ExtractHelper */
    protected $extractHelper;

    public function __construct( ExtractHelper $extractHelper )
    {
        $this->extractHelper = $extractHelper;

        parent::__construct( 'sqli:contentTypesInstaller:extract' );
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName( 'sqli:contentTypesInstaller:extract' )
            ->setDescription( 'Extract Content Types in yml' )
            ->setDefinition(
                array(
                    new InputArgument(
                        'filename',
                        InputArgument::OPTIONAL,
                        'Output filename'
                    ),
                    new InputArgument(
                        'identifierContentType',
                        InputArgument::OPTIONAL,
                        'Identifier contentType to extract'
                    )
                )
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute( InputInterface $input, OutputInterface $output )
    {
        $output->writeln( "Debut de l'extract des content types" );

        $identifierContentType = $input->getArgument( 'identifierContentType' );
        $aContent              = $this->extractHelper->createContentToExport( $identifierContentType, $output );

        // Retrieve parameters for filename
        $outputPathname = $this->getContainer()->getParameter( 'sqli_ez_toolbox.contenttype_installer.installation_directory' );
        $isAbsolutePath = $this->getContainer()->getParameter( 'sqli_ez_toolbox.contenttype_installer.is_absolute_path' );

        if( !$isAbsolutePath )
        {
            // If not an absolute path, generate path from Symfony root dir
            $outputPathname = $this->getContainer()->getParameter( 'kernel.root_dir' ) . '/../' . $outputPathname . '/';
        }

        if( $input->getArgument( 'filename' ) )
        {
            // If a filename specified in argument, write in this file
            $outputFilename = $outputPathname . $input->getArgument( 'filename' );
            // Clear file before first write
            file_put_contents( $outputFilename, "" );
        }

        foreach( $aContent as $sIdentifierContentType => $content )
        {
            if( !$input->getArgument( 'filename' ) )
            {
                // No filename specified so write in separated file
                $outputFilename = $outputPathname . $sIdentifierContentType . ".yml";
                file_put_contents( $outputFilename, "" );
            }

            file_put_contents( $outputFilename, $content, FILE_APPEND );
        }

        $output->writeln( "Fin de l'extract des content types" );
    }
}
