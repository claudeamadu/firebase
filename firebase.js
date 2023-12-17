class FirebaseAuth {
  constructor(apiKey) {
    this.apiKey = apiKey;
    this.baseUrl = 'https://identitytoolkit.googleapis.com/v1/accounts';
    this.localStorageKeys = {
      accessToken: 'firebase_access_token',
      expiresIn: 'firebase_token_expires',
      refreshToken: 'firebase_refresh_token',
      signedIn: 'firebase_signed_in',
      user: 'firebase_user',
    }
  }

  async refreshToken(refreshToken) {
    const url = `https://securetoken.googleapis.com/v1/token?key=${this.apiKey}`;
    const requestData = {
      grant_type: 'refresh_token',
      refresh_token: refreshToken,
    };

    try {
      const response = await fetch(url, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams(requestData),
      });

      const data = await response.json();
      localStorage.setItem(this.localStorageKeys.accessToken, data.access_token);
      localStorage.setItem(this.localStorageKeys.expiresIn, data.expires_in);
      localStorage.setItem(this.localStorageKeys.refreshToken, data.refresh_token);
      return data;
    } catch (error) {
      console.error('Error refreshing token:', error);
      throw error;
    }
  }
  isTokenExpired() {
    let token = localStorage.getItem(this.localStorageKeys.expiresIn)
    if (!token) {
      return true; // Token or expiration time not provided, consider it expired
    }

    const currentTime = Math.floor(Date.now() / 1000); // Current time in seconds
    return token.exp < currentTime;
  }

  // Method to check if a user is signed in
  isUserSignedIn() {
    return localStorage.getItem(this.localStorageKeys.signedIn);
  }

  // Method to get access token from localStorage
  getAccessToken() {
    return localStorage.getItem(this.localStorageKeys.accessToken);
  }

  // Method to get refresh token from localStorage
  getRefreshToken() {
    return localStorage.getItem(this.localStorageKeys.refreshToken);
  }

  currentUser(){
    return new FirebaseUser(JSON.parse(localStorage.getItem(this.localStorageKeys.user)));
  }

  async signUp(email, password) {
    const signUpUrl = `${this.baseUrl}:signUp?key=${this.apiKey}`;
    const requestData = {
      email: email,
      password: password,
      returnSecureToken: true,
    };

    try {
      const response = await fetch(signUpUrl, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify(requestData),
      });

      const data = await response.json();
      return data;
    } catch (error) {
      console.error('Error signing up:', error);
      throw error;
    }
  }

  async signIn(email, password) {
    const signInUrl = `${this.baseUrl}: ?key=${this.apiKey}`;
    const requestData = {
      email: email,
      password: password,
      returnSecureToken: true,
    };

    try {
      const response = await fetch(signInUrl, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify(requestData),
      });

      const data = await response.json();
      localStorage.setItem(this.localStorageKeys.signedIn, true);
      localStorage.setItem(this.localStorageKeys.accessToken, data.idToken);
      localStorage.setItem(this.localStorageKeys.refreshToken, data.refreshToken);
      let user = {displayName: data.displayName,profilePicture: data.profilePicture, email:data.email};
      localStorage.setItem(this.localStorageKeys.user, JSON.stringify(user));
      return data;
    } catch (error) {
      console.error('Error signing in:', error);
      throw error;
    }
  }

  async signOut() {
    localStorage.removeItem(this.localStorageKeys.signedIn);
    localStorage.removeItem(this.localStorageKeys.accessToken);
    localStorage.removeItem(this.localStorageKeys.refreshToken);
    localStorage.removeItem(this.localStorageKeys.expiresIn);
    console.log('Signed Out:', true);
  }

  async sendEmailVerification(idToken) {
    const url = `${this.baseUrl}:sendOobCode?key=${this.apiKey}`;
    const requestData = {
      requestType: 'VERIFY_EMAIL',
      idToken: idToken,
    };

    try {
      const response = await fetch(url, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify(requestData),
      });

      const data = await response.json();
      return data;
    } catch (error) {
      console.error('Error sending email verification:', error);
      throw error;
    }
  }

  async signInAnonymously() {
    const url = `${this.baseUrl}:signUp?key=${this.apiKey}`;
    const requestData = {
      returnSecureToken: true,
    };

    try {
      const response = await fetch(url, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify(requestData),
      });

      const data = await response.json();
      localStorage.setItem(this.localStorageKeys.signedIn, true);
      localStorage.setItem(this.localStorageKeys.accessToken, data.idToken);
      localStorage.setItem(this.localStorageKeys.expiresIn, data.expiresIn);
      localStorage.setItem(this.localStorageKeys.refreshToken, data.refreshToken);
      return data;
    } catch (error) {
      console.error('Error signing in anonymously:', error);
      throw error;
    }
  }

  async sendPasswordResetEmail(email) {
    const url = `${this.baseUrl}:sendOobCode?key=${this.apiKey}`;
    const requestData = {
      email: email,
      requestType: 'PASSWORD_RESET',
    };

    try {
      const response = await fetch(url, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify(requestData),
      });

      const data = await response.json();
      return data;
    } catch (error) {
      console.error('Error sending password reset email:', error);
      throw error;
    }
  }

  async confirmPasswordReset(oobCode, newPassword) {
    const url = `${this.baseUrl}:resetPassword?key=${this.apiKey}`;
    const requestData = {
      oobCode: oobCode,
      newPassword: newPassword,
    };

    try {
      const response = await fetch(url, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify(requestData),
      });

      const data = await response.json();
      return data;
    } catch (error) {
      console.error('Error confirming password reset:', error);
      throw error;
    }
  }

  async changeEmail(idToken, newEmail) {
    const url = `${this.baseUrl}:update?key=${this.apiKey}`;
    const requestData = {
      idToken: idToken,
      email: newEmail,
    };

    try {
      const response = await fetch(url, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify(requestData),
      });

      const data = await response.json();
      return data;
    } catch (error) {
      console.error('Error changing email:', error);
      throw error;
    }
  }

  async changePassword(idToken, newPassword) {
    const url = `${this.baseUrl}:update?key=${this.apiKey}`;
    const requestData = {
      idToken: idToken,
      password: newPassword,
      returnSecureToken: true,
    };

    try {
      const response = await fetch(url, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify(requestData),
      });

      const data = await response.json();
      return data;
    } catch (error) {
      console.error('Error changing password:', error);
      throw error;
    }
  }

  async confirmEmailVerification(oobCode) {
    const url = `${this.baseUrl}:resetPassword?key=${this.apiKey}`;
    const requestData = {
      oobCode: oobCode,
    };

    try {
      const response = await fetch(url, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify(requestData),
      });

      const data = await response.json();
      return data;
    } catch (error) {
      console.error('Error confirming email verification:', error);
      throw error;
    }
  }

  async deleteAccount(idToken) {
    const url = `${this.baseUrl}:delete?key=${this.apiKey}`;
    const requestData = {
      idToken: idToken,
    };

    try {
      const response = await fetch(url, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify(requestData),
      });

      const data = await response.json();
      return data;
    } catch (error) {
      console.error('Error deleting account:', error);
      throw error;
    }
  }
}

class FirebaseUser {
  constructor(user) {
    this.user = user;
    this.displayName = user.displayName;
    this.email = user.email;
    this.profilePicture = user.profilePicture;
  }
}

class FirebaseFirestore {
  constructor(Token, Database, ProjectID) {
    this.Token = Token;
    this.Database = Database;
    this.ProjectID = ProjectID;
    this.baseUrl = `https://firestore.googleapis.com/v1/projects/${this.ProjectID}/databases/${this.Database}`;
    this.db = new FirestoreDB(this.baseUrl, this.Token);
  }
}

class FirestoreDB {
  constructor(baseUrl, token) {
    this.baseUrl = baseUrl;
    this.token = token;
    this.query = new Query(baseUrl, token);
  }

  async getDocument(collectionPath, documentPath) {
    const url = `${this.baseUrl}/documents/${collectionPath}/${documentPath}`;
    const requestOptions = {
      method: 'GET',
      headers: {
        Authorization: `Bearer ${this.token}`,
      },
    };

    try {
      const response = await fetch(url, requestOptions);
      const data = await response.json();
      return convertFirestoreJSON(data);
    } catch (error) {
      console.error('Error getting document:', error);
      throw error;
    }
  }

  async getCollection(collectionPath) {
    const url = `${this.baseUrl}/documents/${collectionPath}`;
    const requestOptions = {
      method: 'GET',
      headers: {
        Authorization: `Bearer ${this.token}`,
      },
    };

    try {
      const response = await fetch(url, requestOptions);
      const data = await response.json();
      return convertFirestoreJSON(data);
    } catch (error) {
      console.error('Error getting collection:', error);
      throw error;
    }
  }

  async createDocument(collection, document, data) {
    const url = `${this.baseUrl}/documents/${collection}/${document}`;
    const requestOptions = {
      method: 'PATCH',
      headers: {
        'Authorization': `Bearer ${this.token}`,
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({
        fields: data
      })
    };

    try {
      const response = await fetch(url, requestOptions);
      const responseData = await response.json();
      return responseData;
    } catch (error) {
      console.error('Error creating document:', error);
      throw error;
    }
  }

  async updateDocument(collection, document, fieldsToUpdate) {
    const fieldPaths = Object.keys(fieldsToUpdate)
      .map(field => `updateMask.fieldPaths=${field}`)
      .join('&');

    const url = `${this.baseUrl}/documents/${collection}/${document}?currentDocument.exists=true&${fieldPaths}&alt=json`;

    const requestOptions = {
      method: 'PATCH',
      headers: {
        'Authorization': `Bearer ${this.token}`,
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({
        fields: fieldsToUpdate,
      })
    };

    try {
      const response = await fetch(url, requestOptions);
      const responseData = await response.json();
      return responseData;
    } catch (error) {
      console.error('Error updating document:', error);
      throw error;
    }
  }


  async deleteDocument(collection, document) {
    const url = `${this.baseUrl}/documents/${collection}/${document}`;
    const requestOptions = {
      method: 'DELETE',
      headers: {
        'Authorization': `Bearer ${this.token}`
      }
    };

    try {
      const response = await fetch(url, requestOptions);
      const responseData = await response.json();
      return responseData;
    } catch (error) {
      console.error('Error deleting document:', error);
      throw error;
    }
  }

  async deleteCollection(collection) {
    const url = `${this.baseUrl}/documents/${collection}`;
    const requestOptions = {
      method: 'DELETE',
      headers: {
        'Authorization': `Bearer ${this.token}`
      }
    };

    try {
      const response = await fetch(url, requestOptions);
      const responseData = await response.json();
      return responseData;
    } catch (error) {
      console.error('Error deleting collection:', error);
      throw error;
    }
  }

  async deleteDocumentFields(collection, document, fieldsToDelete) {
    const url = `${this.baseUrl}/documents/${collection}/${document}`;
    const requestOptions = {
      method: 'PATCH',
      headers: {
        'Authorization': `Bearer ${this.token}`,
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({
        fields: fieldsToDelete
      })
    };

    try {
      const response = await fetch(url, requestOptions);
      const responseData = await response.json();
      return responseData;
    } catch (error) {
      console.error('Error deleting document fields:', error);
      throw error;
    }
  }
}

class Query {
  constructor(baseUrl, token) {
    this.baseUrl = baseUrl;
    this.token = token;
    this.queryMap = {};
    this.fieldsList = [];
    this.orderList = [];
    this.subCollection = null;
  }


  async run() {
    const url = `${this.baseUrl}/documents/:runQuery`;
    const requestOptions = {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${this.token}`,
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({ structuredQuery: this.queryMap }),
    };

    try {
      const response = await fetch(url, requestOptions);
      const responseData = await response.json();
      return convertFirestoreJSON(responseData);
    } catch (error) {
      console.error('Error running query:', error);
      throw error;
    }
  }

  addField(field) {
    this.fieldsList.push({ fieldPath: field });
    return this;
  }

  addFields(fields) {
    fields.forEach(field => {
      this.fieldsList.push({ fieldPath: field });
    });
    return this;
  }

  selectFields() {
    const selectMap = {};
    if (this.fieldsList.length > 0) {
      selectMap.fields = this.fieldsList;
    }
    if (this.orderList.length > 0) {
      this.queryMap.orderBy = this.orderList;
    }
    if (Object.keys(selectMap).length > 0) {
      this.queryMap.select = selectMap;
    }
    return this;
  }

  orderBy(field, direction) {
    this.orderList.push({
      field: { fieldPath: field },
      direction: direction
    });
    return this;
  }

  from(collectionPath) {
    this.selectFields();
    const fromList = [{ collectionId: collectionPath }];
    this.queryMap.from = fromList;
    return this;
  }

  collectionIn(documentPath) {
    this.subCollection = documentPath.complete();
    return this;
  }

  startAt(values) {
    const start = {
      values: values.map(item => convertToFirestoreValue(item))
    };
    this.queryMap.startAt = start;
    return this;
  }

  endAt(values) {
    const end = {
      values: values.map(item => convertToFirestoreValue(item))
    };
    this.queryMap.endAt = end;
    return this;
  }

  offset(position) {
    this.queryMap.offset = position;
    return this;
  }

  limit(limitBy) {
    this.queryMap.limit = limitBy;
    return this;
  }

  /**
   * @param {CompositeFilter} filter - The filter to apply.
   * @returns {Query} - The Query instance.
   */
  where(filter) {
    this.queryMap.where = { compositeFilter: filter.complete() };
    return this;
  }

  /**
   * @param {FieldFilter} filter - The filter to apply.
   * @returns {Query} - The Query instance.
   */
  where2(filter) {
    this.queryMap.where = filter.complete();
    return this;
  }

  /**
   * @param {UnaryFilter} filter - The filter to apply.
   * @returns {Query} - The Query instance.
   */
  where3(filter) {
    this.queryMap.where = { unaryFilter: filter.complete() };
    return this;
  }

  complete() {
    if (Object.keys(this.queryMap).length > 0) {
      return { structuredQuery: this.queryMap };
    } else {
      return {};
    }
  }
}

class FieldFilter {
  constructor() {
    this.filters = [];
    // Operators
    this.LESS_THAN = 'LESS_THAN';
    this.LESS_THAN_OR_EQUAL = 'LESS_THAN_OR_EQUAL';
    this.GREATER_THAN = 'GREATER_THAN';
    this.GREATER_THAN_OR_EQUAL = 'GREATER_THAN_OR_EQUAL';
    this.EQUAL = 'EQUAL';
    this.NOT_EQUAL = 'NOT_EQUAL';
    this.ARRAY_CONTAINS = 'ARRAY_CONTAINS';
    this.ARRAY_CONTAINS_ANY = 'ARRAY_CONTAINS_ANY';
    this.NOT_IN = 'NOT_IN';
    this.IN = 'IN';
  }

  isIn(field, value) {
    const filter = {
      field: { fieldPath: field },
      op: this.IN,
      value: convertToFirestoreValue(value)
    };
    this.filters.push({ fieldFilter: filter });
    return this;
  }

  notIn(field, value) {
    const filter = {
      field: { fieldPath: field },
      op: this.NOT_IN,
      value: convertToFirestoreValue(value)
    };
    this.filters.push({ fieldFilter: filter });
    return this;
  }

  arrayContainsAny(field, value) {
    const filter = {
      field: { fieldPath: field },
      op: this.ARRAY_CONTAINS_ANY,
      value: convertToFirestoreValue(value)
    };
    this.filters.push({ fieldFilter: filter });
    return this;
  }

  arrayContains(field, value) {
    const filter = {
      field: { fieldPath: field },
      op: this.ARRAY_CONTAINS,
      value: convertToFirestoreValue(value)
    };
    this.filters.push({ fieldFilter: filter });
    return this;
  }

  notEqual(field, value) {
    const filter = {
      field: { fieldPath: field },
      op: this.NOT_EQUAL,
      value: convertToFirestoreValue(value)
    };
    this.filters.push({ fieldFilter: filter });
    return this;
  }

  equalTo(field, value) {
    const filter = {
      field: { fieldPath: field },
      op: this.EQUAL,
      value: convertToFirestoreValue(value)
    };
    this.filters.push({ fieldFilter: filter });
    return this;
  }

  lessThan(field, value) {
    const filter = {
      field: { fieldPath: field },
      op: this.LESS_THAN,
      value: convertToFirestoreValue(value)
    };
    this.filters.push({ fieldFilter: filter });
    return this;
  }

  greaterThan(field, value) {
    const filter = {
      field: { fieldPath: field },
      op: this.GREATER_THAN,
      value: convertToFirestoreValue(value)
    };
    this.filters.push({ fieldFilter: filter });
    return this;
  }

  greaterThanOrEqualTo(field, value) {
    const filter = {
      field: { fieldPath: field },
      op: this.GREATER_THAN_OR_EQUAL,
      value: convertToFirestoreValue(value)
    };
    this.filters.push({ fieldFilter: filter });
    return this;
  }

  lessThanOrEqualTo(field, value) {
    const filter = {
      field: { fieldPath: field },
      op: this.LESS_THAN_OR_EQUAL,
      value: convertToFirestoreValue(value)
    };
    this.filters.push({ fieldFilter: filter });
    return this;
  }

  complete() {
    if (this.filters.length >= 2) {
      return this.filters;
    } else {
      return this.filters[0];
    }
  }
}

class CompositeFilter {
  constructor() {
    this.compositeMap = {};
  }

  filters(filter, operator) {
    if (Object.keys(this.compositeMap).length > 0) {
      console.error('CompositeFilter already set');
    } else {
      this.compositeMap.op = operator || 'AND';
      this.compositeMap.filters = filter.complete();
    }
    return this;
  }

  complete() {
    if (Object.keys(this.compositeMap).length > 0) {
      return this.compositeMap;
    } else {
      return {};
    }
  }
}

class UnaryFilter {
  constructor() {
    this.filter = {};
    this.IN = 'IN';
    this.CONTAINS = 'CONTAINS';
    this.IS_NOT_NAN = 'IS_NOT_NAN';
    this.IS_NOT_NULL = 'IS_NOT_NULL';
  }

  setOperator(operator) {
    this.filter.op = operator;
    return this;
  }

  setField(field) {
    this.filter.field = { fieldPath: field };
    return this;
  }

  complete() {
    return this.filter;
  }
}

class PathBuilder {
  constructor() {
    this.pathUrl = '';
  }

  append(path) {
    if (this.pathUrl !== '') {
      this.pathUrl = `${this.pathUrl}/${path}`;
    } else {
      this.pathUrl = path;
    }
    return this;
  }

  collection(path) {
    if (this.pathUrl !== '') {
      this.pathUrl = `${this.pathUrl}/${path}`;
    } else {
      this.pathUrl = path;
    }
    return this;
  }

  document(path) {
    if (this.pathUrl !== '') {
      this.pathUrl = `${this.pathUrl}/${path}`;
    } else {
      this.pathUrl = path;
    }
    return this;
  }

  complete() {
    return this.pathUrl;
  }
}


function convertFirestoreJSON(firestoreData) {
  if (firestoreData.documents) {
    // If it's a collection
    const documents = firestoreData.documents.map(doc => {
      const nameParts = doc.name.split('/');
      const docId = nameParts[nameParts.length - 1]; // Extract document ID
      const fields = this.convertFieldsToJSON(doc.fields); // Convert fields to JSON
      fields.id = docId;
      fields.createTime = doc.createTime;
      fields.updateTime = doc.updateTime;
      return fields;
    });
    return { documents };
  } else if (firestoreData.name && firestoreData.fields) {
    // If it's a single document
    const nameParts = firestoreData.name.split('/');
    const docId = nameParts[nameParts.length - 1]; // Extract document ID
    const fields = this.convertFieldsToJSON(firestoreData.fields); // Convert fields to JSON
    fields.id = docId;
    fields.createTime = firestoreData.createTime;
    fields.updateTime = firestoreData.updateTime;
    return fields;
  } else if (Array.isArray(firestoreData)) {
    const documents = firestoreData.map(element => {
      const doc = element.document;
      const nameParts = doc.name.split('/');
      const docId = nameParts[nameParts.length - 1]; // Extract document ID
      const fields = this.convertFieldsToJSON(doc.fields); // Convert fields to JSON
      fields.id = docId;
      fields.createTime = doc.createTime;
      fields.updateTime = doc.updateTime;
      return fields;
    });
    return { documents };
  }
  return null;
}

// Function to convert Firestore JSON fields to normal JSON
function convertFieldsToJSON(fields) {
  const result = {};
  for (const key in fields) {
    if (fields[key].stringValue !== undefined) {
      result[key] = fields[key].stringValue;
    } else if (fields[key].integerValue !== undefined) {
      result[key] = parseInt(fields[key].integerValue);
    } else if (fields[key].mapValue !== undefined && fields[key].mapValue.fields) {
      result[key] = this.convertFieldsToJSON(fields[key].mapValue.fields);
    } else if (fields[key].arrayValue !== undefined && fields[key].arrayValue.values) {
      result[key] = fields[key].arrayValue.values.map(value => {
        if (value.stringValue !== undefined) {
          return value.stringValue;
        } else if (value.integerValue !== undefined) {
          return parseInt(value.integerValue);
        }
      });
    }
  }
  return result;
}

function convertToFirestoreValue(value) {
  if (typeof value === 'string') {
    return { stringValue: value };
  } else if (typeof value === 'number') {
    return { integerValue: String(value) };
  } else if (Array.isArray(value)) {
    return {
      arrayValue: {
        values: value.map(item => convertToFirestoreValue(item))
      }
    };
  } else if (typeof value === 'object' && value !== null) {
    const keys = Object.keys(value);
    const mapValue = {};
    keys.forEach(key => {
      mapValue[key] = convertToFirestoreValue(value[key]);
    });
    return { mapValue: { fields: mapValue } };
  }
  // Handle other data types or undefined/null values as needed
  return null;
}

function convertFieldsToFirestoreJSON(fields) {
  const result = {};
  for (const key in fields) {
    result[key] = convertToFirestoreValue(fields[key]);
  }
  return result;
}
