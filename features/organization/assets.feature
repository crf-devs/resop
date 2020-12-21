Feature:
    In order to manage the assets in my organization,
    As an admin of an organization,
    I must be able to list, edit and delete assets in my organization.

    Scenario: As an admin of an organization, I can list the assets from my organization
        Given I am authenticated as "admin201@resop.com"
        And I am on "/organizations/201"
        When I follow "Afficher la liste de mes véhicules"
        Then I should be on "/organizations/201/assets/"
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
        When I go to "/organizations/202/assets/"
        Then the response status code should be 403

    Scenario: As an admin of a child organization, I cannot list the assets from the parent organization
        Given I am authenticated as "admin204@resop.com"
        When I go to "/organizations/202/assets/"
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
        When I fill in "commissionable_asset[name]" with "new vehicule"
        And I press "Enregistrer"
        Then I should be on "<list_url>"
        And the response status code should be 200
        And I should see "Véhicule créé"
        And I should see "new vehicule"
        Examples:
            | login              | list_url                                      | preAdd_url                                          | add_url                                          |
            | admin201@resop.com | /organizations/201/assets/?organizationId=203 | /organizations/201/assets/preAdd?organizationId=203 | /organizations/201/assets/add?organizationId=203 |
            | admin204@resop.com | /organizations/204/assets/                    | /organizations/204/assets/preAdd                    | /organizations/204/assets/add                    |

    @javascript
    Scenario: As an admin of an admin of an organization, I can display an asset modal
        Given I am authenticated as "admin201@resop.com"
        When I go to "/organizations/201/assets/"
        And I press "Afficher"
        And I wait for ".modal-show-asset-inner" to be visible
        Then I should see "Modifier"
        And I follow "Modifier"
        Then I should be on "/organizations/201/assets/75992/edit"

    Scenario Outline: As an admin of an organization, I can update an asset from my organization or children organizations
        Given I am authenticated as "<login>"
        When I go to "<edit_url>"
        Then I should be on "<edit_url>"
        And the response status code should be 200
        And the "commissionable_asset_name" field should contain "<name>"
        When I fill in "commissionable_asset[name]" with "new name"
        And I press "Enregistrer"
        Then I should be on "<list_url>"
        And the response status code should be 200
        And I should see "Véhicule \"VPSP - new name\" mis à jour avec succès"
        When I go to "<edit_url>"
        And the "commissionable_asset_name" field should contain "new name"
        Examples:
            | login              | name  | edit_url                             | list_url                                      |
            | admin201@resop.com | 75012 | /organizations/201/assets/75012/edit | /organizations/201/assets/?organizationId=203 |
            | admin204@resop.com | 77102 | /organizations/204/assets/77102/edit | /organizations/204/assets/                    |

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
        When I go to "/organizations/202/assets/"
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
