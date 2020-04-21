Feature:
    In order to manage my children organizations,
    As an admin of a parent organization,
    I must be able to list, edit and create them.

    Scenario: As anonymous, I cannot list the children of an organization
        When I go to "/organizations/201/children/"
        Then I should be on "/login"
        And the response status code should be 200

    Scenario: As an admin of a parent organization, I can list the children of my organization
        Given I am authenticated as "john.doe@resop.com"
        When I go to "/organizations/201"
        Then I should see "Modifier mes structures"
        When I follow "Modifier mes structures"
        Then I should be on "/organizations/201/children/"
        And the response status code should be 200
        And I should see "UL 01-02"

    Scenario: As an admin of an organization without children, I cannot list the children of my organization
        Given I am authenticated as "jane.doe@resop.com"
        When I go to "/organizations/203"
        Then I should not see "Modifier mes structures"
        When I go to "/organizations/203/children/"
        And the response status code should be 403

    Scenario: As an admin of an organization but without a password, I cannot list the children of my organization
        Given I am authenticated as "jane.doe@resop.com"
        When I go to "/organizations/203/children"
        Then the response status code should be 403

    Scenario Outline: As an admin of an organization, I cannot list the children of another organization
        Given I am authenticated as "john.doe@resop.com"
        When I go to "<url>"
        Then the response status code should be 403
        Examples:
            | url                         |
            | /organizations/202/children |
            | /organizations/203/children |
            | /organizations/204/children |

    Scenario: As anonymous, I cannot create an organization
        When I go to "/organizations/201/children/new"
        Then I should be on "/login"
        And the response status code should be 200

    Scenario: As an admin of a parent organization, I can create an organization
        Given I am authenticated as "john.doe@resop.com"
        And I am on "/organizations/201/children/"
        When I follow "Ajouter une structure"
        Then I should be on "/organizations/201/children/new"
        And the response status code should be 200
        When I fill in the following:
            | organization[name] | Lorem ipsum |
        And I press "Valider"
        Then the response status code should be 200
        And I should be on "/organizations/201/children/"
        And I should see "La structure a été ajoutée avec succès."
        And I should see "Lorem ipsum"

    Scenario: As an admin of a children organization, I cannot create an organization
        Given I am authenticated as "jane.doe@resop.com"
        When I go to "/organizations/203"
        Then I should not see "Modifier mes structures"
        When I go to "/organizations/203/children/new"
        Then the response status code should be 403

    Scenario: As an admin of an organization, I cannot create an organization on another one
        Given I am authenticated as "john.doe@resop.com"
        When I go to "/organizations/202/children/new"
        Then the response status code should be 403

    Scenario: As an admin of a parent organization, I can update my children organizations
        Given I am authenticated as "john.doe@resop.com"
        And I am on "/organizations/201/children/"
        When I follow "Modifier"
        Then I should be on "/organizations/201/children/203/edit"
        And the response status code should be 200
        And I should see "Modifier une structure"
        When I fill in the following:
            | organization[name] | Lorem ipsum |
        And I press "Valider"
        Then I should be on "/organizations/201/children/"
        And the response status code should be 200
        And I should see "La structure a été mise à jour avec succès."
        And I should see "Lorem ipsum"

    Scenario: As an admin of an organization, I cannot update an organization I don't have access to
        Given I am authenticated as "john.doe@resop.com"
        When I go to "/organizations/201/children/204/edit"
        Then the response status code should be 403

    Scenario: As anonymous, I cannot update an organization
        When I go to "/organizations/201/children/203/edit"
        Then I should be on "/login"
        And the response status code should be 200

    Scenario: As an admin of an organization, I cannot update my organization
        Given I am authenticated as "jane.doe@resop.com"
        When I go to "/organizations/201/children/203/edit"
        Then the response status code should be 403
