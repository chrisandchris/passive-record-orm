# Deprecations

## To be removed in v3.1.0
*From v3.0.0*
- The class `FilterCondition`, no replacement
- The class `SearchContainer`, no replacement
- The class `ConcreteModel::handleHasResult` in favour of`SqlQuery::requiresResult()`
- The class `ConcreteModel::handleHas` in favour of`SqlQuery::requiresResult()`

# To be removed in v3.2.0
*From v3.0.0*
- The method `Builder::_end()` in favour of `Builder::_endif()`
- The method `Builder::end()` in favour of `Builder::close()`
