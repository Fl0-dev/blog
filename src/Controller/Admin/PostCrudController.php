<?php

namespace App\Controller\Admin;

use App\Entity\Post;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\Form\Extension\Core\Type\RadioType;

class PostCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Post::class;
    }


    public function configureFields(string $pageName): iterable
    {
        return [

            TextField::new('title'),
            AssociationField::new('user'),
            TextEditorField::new('content'),
            BooleanField::new('published'),
            AssociationField::new('categories'),
            DateTimeField::new('created_at')->setFormat('yyyy.MM.dd G HH:mm:ss zzz'),
            DateTimeField::new('updated_at')->setFormat('yyyy.MM.dd G HH:mm:ss zzz')
        ];
    }

}
