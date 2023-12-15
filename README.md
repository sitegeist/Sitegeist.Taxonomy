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

- `Sitegeist.Taxonomy:Root` - The root node at the path `/taxonomies`, allows only vocabulary nodes as children
- `Sitegeist.Taxonomy:Vocabulary` - The root of a hierarchy of meaning, allows only taxonomies nodes as children   
- `Sitegeist.Taxonomy:Taxonomy` - An item in the hierarchy that represents a specific meaning allows only taxonomy
  nodes as children

If you have to enforce the existence of a specific vocabulary or taxonomy, you can use a derived node type:

```YAML
    Vendor.Site:Taxonomy.Root:
      superTypes:
        Sitegeist.Taxonomy:Root: TRUE
      childNodes:
        animals:
          type: 'Sitegeist.Taxonomy:Vocabulary'
```

And configure the taxonomy package to use this root node type instead of the default:

```YAML
    Sitegeist:
      Taxonomy:
        contentRepository:
          rootNodeType: 'Vendor.Site:Taxonomy.Root'
          vocabularyNodeType: 'Sitegeist.Taxonomy:Vocabulary'
          taxonomyNodeType: 'Sitegeist.Taxonomy:Taxonomy'
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
            startingPoint: '/taxonomies'
            placeholder: 'assign Taxonomies'
```

If you want to limit the selectable taxons to a vocabulary or even a taxonomy, then you can configure a more specific
startingPoint:

```YAML
    taxonomyReferences:
      ui:
        inspector:
          editorOptions:
            startingPoint: '/taxonomies/animals/mammals'
```

## Content-Dimensions

Vocabularies and Taxonomies will always be created in all base dimensions. This way, it is ensured that they can
always be referenced. The title and description of a taxons and vocabularies can be translated as is required for
the project.

## CLI Commands

The taxonomy package includes some CLI commands for managing the taxonomies.

- `taxonomy:list` List all taxonomy vocabularies
- `taxonomy:import` Import taxonomy content, expects filename + vocabulary-name (with globbing)
- `taxonomy:export` Export taxonomy content, expects filename + vocabulary-name (with globbing)
- `taxonomy:prune` Prune taxonomy content, expects vocabulary-name (with globbing)

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

```yaml
'Sitegeist.Taxonomy:Taxonomy':
  properties:
    uriPathSegment:
      type: string
```

2. Add the path to your additional `Root.fusion` to the Setting in path `Sitegeist.Taxonomy.backendModule.additionalFusionIncludePathes`.

```yaml
Sitegeist:
  Taxonomy:
    backendModule:
      additionalFusionIncludePathes:
        # Use anything but 0 as key
        uriPathSegment: 'resource://FoobarCom.Site/Private/Fusion/Taxonomy/Property/UriPathSegment.fusion'
```

3. In the Fusion code, define each field as prototype that accepts the props `name` plus `taxonomy` & `defaultTaxonomy` resp. `vocabulary` & `defaultVocabulary`.

```
prototype(FoobarCom.Site:Taxonomy.Property.UriPathSegment) < prototype(Neos.Fusion:Component) {
  name = ''
  taxonomy = ''
  defaultTaxonomy = null

  renderer = afx`
    <div class="neos-control-group">
      <label class="neos-control-label" for="uriPathSegment">
        URI path
        <span @if={props.defaultTaxonomy}>: {props.defaultTaxonomy.properties.description}</span>
      </label>
      <Neos.Fusion.Form:Textfield attributes.required="required" attributes.class="neos-span6 form-inline" field.name="properties[uriPathSegment]"/>
    </div>
  `
}
```

4. Register additional prototype names by adding them to the Settings `Sitegeist.Taxonomy.backendModule.additionalVocabularyFieldPrototypes` or
   `Sitegeist.Taxonomy.backendModule.additionalTaxonomyFieldPrototypes`

```yaml
Sitegeist:
  Taxonomy:
    backendModule:
      additionalTaxonomyFieldPrototypes:
        # Use the property name as key
        uriPathSegment: 'FoobarCom.Site:Taxonomy.Property.UriPathSegment'
```

## Contribution

We will gladly accept contributions. Please send us pull requests.

## License

See [LICENSE](./LICENSE)
