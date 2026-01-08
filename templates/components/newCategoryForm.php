<?php

use App\Entity\Category;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\ComponentToolsTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use Symfony\UX\LiveComponent\LiveResponder;
use Symfony\UX\LiveComponent\ValidatableComponentTrait;

#[AsLiveComponent]
class NewCategoryForm
{
    use ComponentToolsTrait;
    use DefaultActionTrait;
    use ValidatableComponentTrait;

    #[LiveProp(writable: true)]
    #[NotBlank]
    public string $name = '';

    public function saveCategory(EntityManagerInterface $em, LiveResponder $liveResponder): void
    {
        $this->validate();
        $category = new Category();
        $category->setName($this->name);
        $em->persist($category);
        $em->flush();

        $this->dispatchBrowserEvent('modal:close');
        $this->emit('category:created', [
            'category' => $category->getId(),
        ]);

        $this->name = '';
        $this->resetValidation();
    }
}
