---
title: Introduction
weight: 1
group: Getting started
---

[Eupago](https://www.eupago.pt) is a Portuguese payment gateway that lets businesses accept
the payment methods used in Portugal — Multibanco references, MB WAY, PayShop, and more —
through a single API. This package integrates that gateway into Laravel, end to end:

- **Create payments** for Multibanco (MB), MB WAY, PayShop, and PaysafeCard.
- **Persist payment references** as Eloquent models, attached to any model of yours (an
  `Order`, an `Invoice`, …) through ready-made traits.
- **Handle Eupago's webhooks** out of the box: the package ships the callback endpoints,
  validates the payload, marks the reference as paid, and fires an event
  (`MBReferencePaid`, `MBWayReferencePaid`, `PayShopReferencePaid`,
  `PaysafeCardReferencePaid`) you can hook your business logic on.
- **Query a reference's status** on demand, for reconciliation or missed callbacks.

You can use it as a **full integration** (traits, models, webhooks) or as a **thin API
client** (payment classes only) — see [Routes](configuration.md#routes) for how to switch.

## How it works

Every payment method follows the same pattern: build the payment object, call `create()`
to run the request against the Eupago API, and persist the returned reference data — or
let the method's trait do the create-and-persist in a single call. When Eupago confirms
the payment, the package [handles the callback](callbacks.md) and fires the method's
`*ReferencePaid` event.
