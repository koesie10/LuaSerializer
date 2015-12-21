<?php
/**
 * LuaFormErrorHandler.php
 *
 * @author Koen Vlaswinkel <koen@vlaswinkel.info>
 * @since  21/12/2015 11:27
 */

namespace Vlaswinkel\Lua\JMS;

use JMS\Serializer\GenericSerializationVisitor;
use JMS\Serializer\GraphNavigator;
use JMS\Serializer\Handler\SubscribingHandlerInterface;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormError;
use Symfony\Component\Translation\TranslatorInterface;

class LuaFormHandler implements SubscribingHandlerInterface {
    private $translator;

    public static function getSubscribingMethods() {
        $methods   = [];
        $methods[] = [
            'direction' => GraphNavigator::DIRECTION_SERIALIZATION,
            'type'      => 'Symfony\Component\Form\Form',
            'format'    => 'lua',
        ];
        $methods[] = [
            'direction' => GraphNavigator::DIRECTION_SERIALIZATION,
            'type'      => 'Symfony\Component\Form\FormError',
            'format'    => 'lua',
        ];
        return $methods;
    }

    public function __construct(TranslatorInterface $translator) {
        $this->translator = $translator;
    }

    public function serializeFormToLua(LuaSerializationVisitor $visitor, Form $form, array $type) {
        return $this->convertFormToArray($visitor, $form);
    }

    public function serializeFormErrorToLua(LuaSerializationVisitor $visitor, FormError $formError, array $type) {
        return $this->getErrorMessage($formError);
    }

    private function getErrorMessage(FormError $error) {
        if (null !== $error->getMessagePluralization()) {
            return $this->translator->transChoice(
                $error->getMessageTemplate(),
                $error->getMessagePluralization(),
                $error->getMessageParameters(),
                'validators'
            );
        }
        return $this->translator->trans($error->getMessageTemplate(), $error->getMessageParameters(), 'validators');
    }

    private function convertFormToArray(GenericSerializationVisitor $visitor, Form $data) {
        $isRoot = null === $visitor->getRoot();
        $form   = new \ArrayObject();
        $errors = [];
        foreach ($data->getErrors() as $error) {
            $errors[] = $this->getErrorMessage($error);
        }
        if (!empty($errors)) {
            $form['errors'] = $errors;
        }
        $children = [];
        foreach ($data->all() as $child) {
            if ($child instanceof Form) {
                $children[$child->getName()] = $this->convertFormToArray($visitor, $child);
            }
        }
        if (!empty($children)) {
            $form['children'] = $children;
        }
        if ($isRoot) {
            $visitor->setRoot($form);
        }
        return $form;
    }
}