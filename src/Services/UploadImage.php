<?php




namespace App\Services;

    use Symfony\Component\String\Slugger\SluggerInterface;

class UploadImage

{
    private $slugger;

    /**
     * Permet l'injection de dépendance nativement
     * @param SluggerInterface $slugger
     */
    public function __construct(SluggerInterface $slugger)
    {
        $this->slugger = $slugger;
    }

    /**
     * Permet de changer le nom du fichier lors de l'upload d'image
     * @param $dossierPhotos
     * @return string
     */
    public function uploadImage($dossierPhotos)
    {
        $nomOriginalDeFichier = pathinfo($dossierPhotos->getClientOriginalName(), PATHINFO_FILENAME);
        //on change le nom du fichier
        $nomDeFichierSecur = $this->slugger->slug($nomOriginalDeFichier);
        $nomDeFichier = $nomDeFichierSecur . '-' . uniqid() . '.' . $dossierPhotos->guessExtension();
        return $nomDeFichier;
    }
}
