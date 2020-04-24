Feature:
    In order to manage the assets in my organization,
    As an organization,
    I must be able to list, edit and delete assets in my organization.

    Scenario: As anonymous, I cannot list the assets from an organization
        When I go to "/organizations/10/assets"
        Then I should be on "/organizations/login"
        And the response status code should be 200

    Scenario: As an organization, I can list the assets from my organization
        Given I am authenticated as "DT75"
        And I am on "/organizations"
        And the response status code should be 200
        When I follow "Afficher la liste de mes véhicules"
        Then I should be on "/organizations/10/assets/"
        And the response status code should be 200
        And I should see "75992"
        And I should see "75996"
        And I should not see "7799"
        And I should not see "7501"
        And I should not see "7710"

    Scenario: As a parent organization, I can list the assets from my children organizations
        Given I am authenticated as "DT75"
        And I am on "/organizations"
        And the response status code should be 200
        When I follow "Modifier mes structures"
        Then I should be on "/organizations/children"
        And the response status code should be 200
        When I follow "Liste des véhicules"
        Then I should be on "/organizations/30/assets/"
        And the response status code should be 200
        And I should see "75012"
        And I should see "75016"
        And I should not see "7599"
        And I should not see "7799"
        And I should not see "7710"

    Scenario: As a parent organization, I cannot list the assets from another organization
        Given I am authenticated as "DT75"
        When I go to "/organizations/20/assets"
        Then the response status code should be 403

    Scenario Outline: As an authenticated parent organization, I can add an asset from my organization or children organizations
        Given I am authenticated as "<login>"
        When I go to "/organizations/30/assets"
        And I follow "Ajouter un nouveau véhicule"
        Then I should be on "/organizations/30/assets/add"
        And the response status code should be 200
        When I fill in the following:
            | commissionable_asset[type]            | VL                    |
            | commissionable_asset[name]            | new vehicule          |
            | commissionable_asset[hasMobileRadio]  | 1                     |
            | commissionable_asset[hasFirstAidKit]  | 1                     |
            | commissionable_asset[parkingLocation] | some parking location |
            | commissionable_asset[contact]         | some contact          |
            | commissionable_asset[seatingCapacity] | 5                     |
            | commissionable_asset[licensePlate]    | some license plate    |
            | commissionable_asset[comments]        | some comments         |
        And I press "Enregistrer"
        Then I should be on "/organizations/30/assets/"
        And the response status code should be 200
        And I should see "Véhicule créé"
        And I should see "VL - new vehicule"
        When I follow "Modifier" at position -1
        Then the response status code should be 200
        And the "commissionable_asset_type" field should contain "VL"
        And the "commissionable_asset_name" field should contain "new vehicule"
        And the "commissionable_asset_hasMobileRadio_0" checkbox is checked
        And the "commissionable_asset_hasFirstAidKit_0" checkbox is checked
        And the "commissionable_asset_parkingLocation" field should contain "some parking location"
        And the "commissionable_asset_contact" field should contain "some contact"
        And the "commissionable_asset_seatingCapacity" field should contain "5"
        And the "commissionable_asset_licensePlate" field should contain "some license plate"
        And the "commissionable_asset_comments" field should contain "some comments"
        Examples:
            | login    |
            | DT75     |
            | UL 01-02 |

    Scenario Outline: As a parent organization, I can update an asset from my organization or children organizations
        Given I am authenticated as "<login>"
        When I go to "/organizations/30/assets"
        And I follow "Modifier"
        Then I should be on "/organizations/30/assets/75012/edit"
        And the response status code should be 200
        And the "commissionable_asset_type" field should contain "VPSP"
        And the "commissionable_asset_name" field should contain "75012"
        When I fill in the following:
            | commissionable_asset[type] | VL       |
            | commissionable_asset[name] | new name |
        And I press "Enregistrer"
        Then I should be on "/organizations/30/assets/"
        And the response status code should be 200
        And I should see "Véhicule \"VL - new name\" mis à jour avec succès"
        When I go to "/organizations/30/assets/75012/edit"
        Then I should be on "/organizations/30/assets/75012/edit"
        And the response status code should be 200
        And the "commissionable_asset_type" field should contain "VL"
        And the "commissionable_asset_name" field should contain "new name"
        Examples:
            | login    |
            | DT75     |
            | UL 01-02 |

    Scenario: As a parent organization, I cannot update an asset from another organizations
        Given I am authenticated as "DT75"
        When I go to "/organizations/20/assets/77992/edit"
        Then the response status code should be 403

    Scenario: As an admin of a child organization, I cannot update an asset from the parent organization
        Given I am authenticated as "UL 01-02"
        When I go to "/organizations/10/assets/75992/edit"
        Then the response status code should be 403

    Scenario: As a parent organization, I cannot update an asset with a mismatched url
        Given I am authenticated as "DT75"
        When I go to "/organizations/10/assets/75012/edit"
        Then the response status code should be 404

#    @javascript
#    Scenario Outline: As a parent organization, I can delete an asset from my organization or children organizations
#        Given I am authenticated as "<login>"
#        When I go to "/organizations/30/assets"
#        And I press "Supprimer"
#        Then I should see "Vous êtes sur le point de supprimer le véhicule : VPSP - 75992 et toutes ses disponibilités."
#        When I press "Supprimer"
#        Then I should be on "/organizations/30/assets"
#        And the response status code should be 200
#        And I should see "Le véhicule a été supprimé avec succès."
#        And I should not see "75992"
#        Examples:
#            | login    |
#            | DT75     |
#            | UL 01-02 |

#    Scenario: As a parent organization, I cannot directly delete an asset from my organization
#        Given I am authenticated as "john.doe@resop.com"
#        When I go to "/organizations/10/assets/75992/delete"
#        Then the response status code should be 405

    Scenario: As a parent organization, I cannot delete an asset from another organization
        Given I am authenticated as "DT75"
        When I go to "/organizations/20/assets"
        Then the response status code should be 403
        When I go to "/organizations/20/assets/77992/delete"
        Then the response status code should be 403

    Scenario: As a parent organization, I cannot access availability of an asset with a mismatched url
        Given I am authenticated as "john.doe@resop.com"
        When I go to "/organizations/10/availability/75012/2020-W10"
        Then the response status code should be 404
