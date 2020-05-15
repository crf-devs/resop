Feature:
    In order to manage the assets in my organization,
    As an admin of an organization,
    I must be able to list, edit and delete assets in my organization.

    Scenario: As an admin of an organization, I can list the assets from my organization
        Given I am authenticated as "admin203@resop.com"
        And I am on "/organizations/203"
        When I follow "Afficher la liste de mes véhicules"
        Then I should be on "/organizations/203/assets"
        And the response status code should be 200
        And I should see "75992"
        And I should see "75996"
        And I should not see "7799"
        And I should not see "7501"
        And I should not see "7710"

    Scenario: As an admin of a parent organization, I can list the assets from my children organizations
        Given I am authenticated as "admin201@resop.com"
        And I am on "/organizations/201"
        When I follow "Modifier mes structures"
        Then I should be on "/organizations/201/children/"
        And the response status code should be 200
        When I follow "Liste des véhicules"
        Then I should be on "/organizations/201/assets/?organizationId=203"
        And the response status code should be 200
        And I should see "75012"
        And I should see "75016"
        And I should not see "7599"
        And I should not see "7799"
        And I should not see "7710"

    Scenario: As an admin of an organization, I cannot list the assets from an organization I don't have access to
        Given I am authenticated as "admin201@resop.com"
        When I go to "/organizations/202/assets"
        Then the response status code should be 403

    Scenario: As an admin of a child organization, I cannot list the assets from the parent organization
        Given I am authenticated as "admin203@resop.com"
        When I go to "/organizations/201/assets"
        Then the response status code should be 403

    Scenario Outline: As an admin of an organization, I can add an asset on my organization or children organizations
        Given I am authenticated as "<login>"
        When I go to "<list_url>"
        And I follow "Ajouter un nouveau véhicule"
        Then the response status code should be 200
        And I should be on "<preAdd_url>"
        When I select "VL" from "type"
        And I press "Continuer"
        Then the response status code should be 200
        And I should be on "<add_url>"
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
        Then I should be on "<list_url>"
        And the response status code should be 200
        And I should see "Véhicule créé"
        And I should see "VL - new vehicule"
        When I follow the last "Modifier"
        Then I should be on "/organizations/203/assets/1/edit"
        And the response status code should be 200
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
            | login              | list_url                  | preAdd_url                       | add_url                       |
#            todo: there is a bug when using parent organization: https://github.com/crf-devs/resop/issues/360
#            todo: how to create a new asset on a children organization (but not on current one)?
#            | admin201@resop.com     | /organizations/201/assets?organization=203 | /organizations/201/assets/preAdd | /organizations/201/assets/add |
            | admin203@resop.com | /organizations/203/assets | /organizations/203/assets/preAdd | /organizations/203/assets/add |

#    TODO Fix this test
#    @javascript
#    Scenario: As an admin of an organization, I can display an asset modal
#        Given I am authenticated as "admin201@resop.com"
#        When I go to "/organizations/201/assets"
#        And I press "Afficher"
#        And I wait for ".ajax-modal-content" to be visible
#        Then I should see "Modifier"
#        And I follow "Modifier"
#        Then I should be on "/organizations/201/assets/75012/edit"

    Scenario Outline: As an admin of an organization, I can update an asset from my organization or children organizations
        Given I am authenticated as "<login>"
        When I go to "<edit_url>"
        Then I should be on "<edit_url>"
        And the response status code should be 200
        And the "commissionable_asset_name" field should contain "75012"
        When I fill in the following:
            | commissionable_asset[name] | new name |
        And I press "Enregistrer"
        Then I should be on "<list_url>"
        And the response status code should be 200
        And I should see "Véhicule \"VPSP - new name\" mis à jour avec succès"
        When I go to "<edit_url>"
        And the "commissionable_asset_name" field should contain "new name"
        Examples:
            | login              | edit_url                             | list_url                  |
#            todo: there is a bug when using parent organization: https://github.com/crf-devs/resop/issues/360
#            | admin201@resop.com     | /organizations/201/assets/75012/edit | /organizations/201/assets?organization=203 |
            | admin203@resop.com | /organizations/203/assets/75012/edit | /organizations/203/assets |

    Scenario: As an admin of a parent organization, I cannot update an asset from an organization I don't have access to
        Given I am authenticated as "admin201@resop.com"
        When I go to "/organizations/202/assets/77992/edit"
        Then the response status code should be 403

    Scenario: As an admin of a child organization, I cannot update an asset on the parent organization
        Given I am authenticated as "admin203@resop.com"
        When I go to "/organizations/201/assets/75992/edit"
        Then the response status code should be 403

    Scenario: As an admin of a parent organization, I cannot update an invalid asset
        Given I am authenticated as "admin201@resop.com"
        When I go to "/organizations/201/assets/77102/edit"
        Then the response status code should be 404

    @javascript
    Scenario: As an admin of a parent organization, I can delete an asset from my organization or children organizations
        Given I am authenticated as "admin201@resop.com"
        And I go to "/organizations/201/assets/75012/edit"
        When I follow "Supprimer"
        And I wait for "#delete-item-modal" to be visible
        Then I should see "Vous êtes sur le point de supprimer le véhicule suivant et toutes ses disponibilités : VPSP - 75012"
        When I press "Supprimer"
        Then I should be on "/organizations/201/assets/?organizationId=203"
        And I should see "Le véhicule a été supprimé avec succès."
        And I should not see "75012"

#    https://github.com/crf-devs/resop/issues/348
#    Scenario: As an admin of a parent organization, I cannot directly delete an asset from my organization
#        Given I am authenticated as "admin201@resop.com"
#        When I go to "/organizations/201/assets/75992/delete"
#        Then the response status code should be 405

    Scenario: As an admin of a parent organization, I cannot delete an asset from another organization
        Given I am authenticated as "admin201@resop.com"
        When I go to "/organizations/202/assets"
        Then the response status code should be 403
        When I go to "/organizations/202/assets/77992/delete"
        Then the response status code should be 403

    Scenario: As an admin of a parent organization, I cannot access availability of an invalid asset
        Given I am authenticated as "admin201@resop.com"
        When I go to "/organizations/201/availability/75012/2020-W10"
        Then the response status code should be 404

    Scenario: As an admin of an organization, I cannot access availability of an asset with a mismatched url
        Given I am authenticated as "admin201@resop.com"
        When I go to "/organizations/201/availability/75012/2020-W10"
        Then the response status code should be 404
