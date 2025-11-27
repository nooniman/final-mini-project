/**
 * Sign In Screen
 * Login screen for student authentication
 */

import React, { useState } from 'react';
import {
  View,
  StyleSheet,
  KeyboardAvoidingView,
  Platform,
  ScrollView,
  Image,
  TouchableOpacity,
} from 'react-native';
import { router } from 'expo-router';
import { useSafeAreaInsets } from 'react-native-safe-area-context';
import { Ionicons } from '@expo/vector-icons';
import { useTheme } from '../src/context/ThemeContext';
import { useAuthStore } from '../src/store/useAuthStore';
import { Layout } from '../src/constants/Layout';
import {
  ThemedView,
  ThemedText,
  Input,
  Button,
  Card,
} from '../src/components/ui';

export default function SignInScreen() {
  const { colors, isDark } = useTheme();
  const insets = useSafeAreaInsets();
  const { login, isLoading, error, clearError } = useAuthStore();

  const [studentId, setStudentId] = useState('');
  const [password, setPassword] = useState('');
  const [rememberMe, setRememberMe] = useState(false);

  const handleLogin = async () => {
    if (!studentId.trim() || !password.trim()) {
      return;
    }

    clearError();
    const success = await login({
      studentId: studentId.trim(),
      password,
      rememberMe,
    });

    if (success) {
      router.replace('/(app)/(tabs)/dashboard');
    }
  };

  // For demo purposes - remove in production
  const handleDemoLogin = async () => {
    setStudentId('2024-00001');
    setPassword('password123');
  };

  return (
    <ThemedView style={styles.container}>
      <KeyboardAvoidingView
        style={styles.keyboardView}
        behavior={Platform.OS === 'ios' ? 'padding' : 'height'}
      >
        <ScrollView
          contentContainerStyle={[
            styles.scrollContent,
            { paddingTop: insets.top + 20, paddingBottom: insets.bottom + 20 },
          ]}
          keyboardShouldPersistTaps="handled"
          showsVerticalScrollIndicator={false}
        >
          {/* Logo and Branding */}
          <View style={styles.header}>
            <View
              style={[
                styles.logoContainer,
                { backgroundColor: colors.primaryMuted },
              ]}
            >
              <Ionicons name="school" size={48} color={colors.primary} />
            </View>
            <ThemedText variant="title1" style={styles.title}>
              WMSU
            </ThemedText>
            <ThemedText variant="title3" color="secondary">
              Grading System
            </ThemedText>
            <ThemedText
              variant="subheadline"
              color="tertiary"
              style={styles.subtitle}
            >
              Western Mindanao State University
            </ThemedText>
          </View>

          {/* Login Form */}
          <Card variant="elevated" style={styles.formCard}>
            <ThemedText variant="title3" style={styles.formTitle}>
              Student Login
            </ThemedText>

            {error && (
              <View
                style={[
                  styles.errorContainer,
                  { backgroundColor: colors.errorLight },
                ]}
              >
                <Ionicons name="alert-circle" size={20} color={colors.error} />
                <ThemedText
                  variant="subheadline"
                  style={[styles.errorText, { color: colors.error }]}
                >
                  {error}
                </ThemedText>
              </View>
            )}

            <Input
              label="Student ID"
              placeholder="Enter your student ID"
              value={studentId}
              onChangeText={setStudentId}
              leftIcon="person-outline"
              autoCapitalize="none"
              autoCorrect={false}
              keyboardType="default"
            />

            <Input
              label="Password"
              placeholder="Enter your password"
              value={password}
              onChangeText={setPassword}
              leftIcon="lock-closed-outline"
              secureTextEntry
              autoCapitalize="none"
            />

            <TouchableOpacity
              style={styles.rememberRow}
              onPress={() => setRememberMe(!rememberMe)}
            >
              <Ionicons
                name={rememberMe ? 'checkbox' : 'square-outline'}
                size={22}
                color={rememberMe ? colors.primary : colors.textTertiary}
              />
              <ThemedText variant="subheadline" style={styles.rememberText}>
                Remember me
              </ThemedText>
            </TouchableOpacity>

            <Button
              title="Sign In"
              onPress={handleLogin}
              loading={isLoading}
              disabled={!studentId.trim() || !password.trim()}
              fullWidth
              style={styles.loginButton}
            />

            <TouchableOpacity style={styles.forgotPassword}>
              <ThemedText variant="subheadline" color="accent">
                Forgot Password?
              </ThemedText>
            </TouchableOpacity>
          </Card>

          {/* Demo Login Button - Remove in production */}
          <TouchableOpacity
            style={styles.demoButton}
            onPress={handleDemoLogin}
          >
            <ThemedText variant="footnote" color="tertiary">
              Use Demo Credentials
            </ThemedText>
          </TouchableOpacity>

          {/* Footer */}
          <View style={styles.footer}>
            <ThemedText variant="caption1" color="muted" align="center">
              © 2024 Western Mindanao State University
            </ThemedText>
            <ThemedText variant="caption2" color="muted" align="center">
              All rights reserved
            </ThemedText>
          </View>
        </ScrollView>
      </KeyboardAvoidingView>
    </ThemedView>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
  },
  keyboardView: {
    flex: 1,
  },
  scrollContent: {
    flexGrow: 1,
    paddingHorizontal: Layout.padding.screen,
  },
  header: {
    alignItems: 'center',
    marginBottom: Layout.spacing.xl,
  },
  logoContainer: {
    width: 100,
    height: 100,
    borderRadius: 50,
    alignItems: 'center',
    justifyContent: 'center',
    marginBottom: Layout.spacing.md,
  },
  title: {
    marginBottom: Layout.spacing.xs,
  },
  subtitle: {
    marginTop: Layout.spacing.sm,
  },
  formCard: {
    marginBottom: Layout.spacing.lg,
  },
  formTitle: {
    marginBottom: Layout.spacing.lg,
    textAlign: 'center',
  },
  errorContainer: {
    flexDirection: 'row',
    alignItems: 'center',
    padding: Layout.spacing.sm,
    borderRadius: Layout.borderRadius.sm,
    marginBottom: Layout.spacing.md,
  },
  errorText: {
    marginLeft: Layout.spacing.sm,
    flex: 1,
  },
  rememberRow: {
    flexDirection: 'row',
    alignItems: 'center',
    marginBottom: Layout.spacing.lg,
  },
  rememberText: {
    marginLeft: Layout.spacing.sm,
  },
  loginButton: {
    marginBottom: Layout.spacing.md,
  },
  forgotPassword: {
    alignItems: 'center',
  },
  demoButton: {
    alignItems: 'center',
    padding: Layout.spacing.md,
  },
  footer: {
    marginTop: 'auto',
    paddingTop: Layout.spacing.xl,
  },
});
