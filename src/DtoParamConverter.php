<?php

    declare( strict_types = 1 );

    namespace App;

    use \ReflectionClass;
    use \Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
    use \Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
    use \Symfony\Component\HttpFoundation\Request;
    use \Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
    use \Symfony\Component\Serializer\Encoder\JsonEncoder;
    use \Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;
    use \Symfony\Component\Serializer\Serializer;
    use \Symfony\Component\Validator\Validator\ValidatorInterface;

    class DtoParamConverter
    implements ParamConverterInterface {

        private ValidatorInterface $validator;

        public function __construct( ValidatorInterface $validator ) {
            $this->validator = $validator;
        }

        public function apply( Request $request, ParamConverter $configuration ): bool {

            $class = $configuration->getClass();
            $data  = $request->getContent( asResource: false );

            if ( empty( $data ) ) {
                throw new BadRequestHttpException( 'request payload empty' );
            }

            $serializer = new Serializer( [ new GetSetMethodNormalizer() ], [ new JsonEncoder() ] );

            // map requestbody on DTO
            $dto = $serializer->deserialize( $data, $class, 'json' );

            $validationErrors = $this->validator->validate( $dto );
            if ( count( $validationErrors ) > 0 ) {
                throw (new DtoValidationException( 'DTO Validation failed' ) )->setValidationErrors( $validationErrors );
            }

            // append DTO instance to request attributes
            $request->attributes->set( $configuration->getName(), $dto );

            return true;
        }

        public function supports( ParamConverter $configuration ): bool {

            $class = $configuration->getClass();

            // no typehint provided
            if ( !$class ) {
                return false;
            }

            /**
             * we support only DTOs
             *
             * since its derived from typehints, we can always assume valid classstrings
             * and ignore the false positive
             * @phpstan-ignore-next-line
             */
            $reflection = new ReflectionClass( $class );
            return $reflection->implementsInterface( DtoInterface::class );
        }

    }
