# Sitegeist.Taxonomy

> Manage vocabularies and taxonomies in Neos as node in a separate subtree `/taxonomies` distinct from `/sites`

### Authors & Sponsors

* Martin Ficzel - ficzel@sitegeist.de

*The development and the public releases of this package is generously sponsored by our employer http://www.sitegeist.de.*

## About

If taxonomies are defined as documents in the `/sites` subtree of the Neos ContentRepository, things tend to get
complicated:

 - If the meaning of an item is determined by its position in a hierarchy, it is complicated to add more than one
   relation or relations for different contexts.
 - Mixing Taxonomies (meaning) with content (presentation) leads to a complicated structure that often is hard to
   comprehend for editors.
 - It is hard to share taxonomies across multiple sites.
 - It is hard to ensure taxonomies exist in all needed dimensions since this also affects the site structure.
 - Limiting read and write access to taxonomies inside sites is possible but not trivial.

Sitegeist.Taxonomies store vocabularies and taxonomies as nodes outside of the `/sites` hierarchy in the content
repository. This way, the meaning of the taxonomy relations can be expressed better, taxonomies can be used across
multiple sites and the taxonomy documents can be defined without interfering with the taxonomy meaning.

It also provides a separate backend module for managing vocabularies and taxonomies.

## Installation

Sitegeist.Taxonomy is available via packagist `composer require sitegeist/taxonomy`.
We use semantic-versioning, so every breaking change will increase the major version number.

## Storing vocabularies and taxonomies in the ContentRepository

Sitegeist.Taxonomy defines three basic node types:

- `Sitegeist.Taxonomy:Root` - The root node at the path `/<Sitegeist.Taxonomy:Root>`, allows only vocabulary nodes as children
- `Sitegeist.Taxonomy:Vocabulary` - The root of a hierarchy of meaning, allows only taxonomies nodes as children   
- `Sitegeist.Taxonomy:Taxonomy` - An item in the hierarchy that represents a specific meaning allows only taxonomy
  nodes as children

If you have to enforce the existence of a specific vocabulary or taxonomy, you can define them as children of the taxonomy root.

```YAML
    Sitegeist.Taxonomy:Root:
      childNodes:
        animals:
          type: 'Sitegeist.Taxonomy:Vocabulary'
```

## Referencing taxonomies

Since taxonomies are nodes, they are simply referenced via `reference` or `references` properties:

```YAML
    taxonomyReferences:
      type: references
      ui:
        label: 'Taxonomy References'
        inspector:
          group: taxonomy
          editorOptions:
            nodeTypes: ['Sitegeist.Taxonomy:Taxonomy']
            startingPoint: '/<Sitegeist.Taxonomy:Root>'
            placeholder: 'assign Taxonomies'
```

If you want to limit the selectable taxons to a vocabulary or even a taxonomy, then you can configure a more specific
startingPoint:

```YAML
    taxonomyReferences:
      ui:
        inspector:
          editorOptions:
            startingPoint: '/<Sitegeist.Taxonomy:Root>/animals/mammals'
```

## Content-Dimensions

Vocabularies and Taxonomies will always be created in all base dimensions. This way, it is ensured that they can
always be referenced. The title and description of a taxons and vocabularies can be translated as is required for
the project.

## FlowQuery Operations

The package contains some special flowQuery operations that work on taxomomy-nodes
and allow for easy traversal and collecting of ancestors and descendants.

- `taxonomyAncestors()` - find the taxon-nodes above the given taxon-nodes
- `taxonomyWithAncestors()` - find the taxon-nodes above the given taxon-nodes but include those
- `taxonomyDescendants()` - find the taxon-nodes below the given taxon-nodes
- `taxonomyWithDescendants()` - find the taxon-nodes below the given taxon-nodes but include those
- `taxonomyVocabularies()` - find the vocabulary-nodes for the given taxon-nodes

## CLI Commands

The taxonomy package includes some CLI commands for managing the taxonomies.

- `taxonomy:vocabularies` List all vocabularies
- `taxonomy:taxonomies` List taxonomies inside a vocabulary

## Privileges

Sitegeist.Taxonomy brings the following privilege targets to allow you to restrict read access, management and editing
of taxonomies:

- `Sitegeist.Taxonomy:Module.Show` Show the backend Module and explore the existing taxonomies by default granted to Editors.
- `Sitegeist.Taxonomy:Module.ManageVocabularyActions` Add, edit and delete vocabularies. By default granted to Administrators.
- `Sitegeist.Taxonomy:Module.ManageTaxonomyActions` Add, edit and delete taxons. By default granted to Administrators.

Reading and referencing taxonomies from other nodes is currently not limited.

## Extensibility

Packages can add additional fields to the forms of taxonomies and vocabularies. To do this 
the following steps are required.

1. Extend the NodeTypes `Sitegeist.Taxonomy:Taxonomy` or `Sitegeist.Taxonomy:Vocabulary` in your package.
2. Add tha path to your additional `Root.fusion` to the Setting in path `Sitegeist.Taxonomy.backendModule.additionalFusionIncludePathes`.
3. In the fusion code define each field as prototype that accepts the props `name` plus `taxon` & `defaultTaxon` resp. `vocabulary` & `defaultVocabulary`. 
4. Register addtional prototypesNames by adding them to the Settings `Sitegeist.Taxonomy.backendModule.additionalVocabularyFieldPrototypes` or
   `Sitegeist.Taxonomy.backendModule.additionalTaxonomyFieldPrototypes`

## Contribution

We will gladly accept contributions. Please send us pull requests.

## License

See [LICENSE](./LICENSE)
